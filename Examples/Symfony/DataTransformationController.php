<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FormGenerator\V2\Builder\FormBuilder;
use FormGenerator\V2\DataTransformer\DateTimeToStringTransformer;
use FormGenerator\V2\DataTransformer\StringToArrayTransformer;
use FormGenerator\V2\DataTransformer\CallbackTransformer;
use FormGenerator\V2\Renderer\TwigRenderer;
use FormGenerator\V2\Theme\Bootstrap5Theme;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Example: Symfony Controller with Data Transformation
 *
 * Demonstrates how to use Data Transformers in a Symfony application
 * to handle complex data types like DateTime, arrays, and entity relationships.
 *
 * @since 2.3.1
 */
class DataTransformationController extends AbstractController
{
    public function __construct(
        private readonly TwigRenderer $formRenderer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Display and process user form with data transformations
     *
     * @Route("/user/edit/{id}", name="user_edit")
     */
    public function editUser(int $id, Request $request): Response
    {
        // Load user from database
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Extract model data (with proper types from entity)
        $userData = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'birthday' => $user->getBirthday(),          // DateTime object
            'roles' => $user->getRoles(),                 // Array
            'department' => $user->getDepartment(),       // Entity object
            'preferences' => $user->getPreferences(),     // JSON array
            'createdAt' => $user->getCreatedAt(),        // DateTimeImmutable
        ];

        // Build form with data transformers
        $form = FormBuilder::create('user_edit_form')
            ->setRenderer($this->formRenderer)
            ->setTheme(new Bootstrap5Theme())
            ->setAction($this->generateUrl('user_update', ['id' => $id]))
            ->setData($userData);

        // Hidden ID field
        $form->addHidden('id', $user->getId())->add();

        // Text inputs (no transformation)
        $form->addText('name', 'Full Name')
            ->required()
            ->minLength(3)
            ->add();

        $form->addEmail('email', 'Email Address')
            ->required()
            ->unique('users', 'email', $user->getId())
            ->add();

        // DateTime transformation
        $form->addDate('birthday', 'Date of Birth')
            ->addTransformer(new DateTimeToStringTransformer('Y-m-d'))
            ->required()
            ->add();

        // Array -> String transformation for roles
        $form->addText('roles', 'User Roles')
            ->addTransformer(new StringToArrayTransformer(', '))
            ->helpText('Enter roles separated by commas (e.g., ROLE_USER, ROLE_ADMIN)')
            ->add();

        // Entity -> ID transformation (select department by ID)
        $departmentRepo = $this->entityManager->getRepository(Department::class);
        $departments = $departmentRepo->findAll();
        $departmentOptions = [];
        foreach ($departments as $dept) {
            $departmentOptions[$dept->getId()] = $dept->getName();
        }

        $form->addSelect('department', 'Department')
            ->options($departmentOptions)
            ->addTransformer(new CallbackTransformer(
                // Transform: Entity -> ID
                transform: fn($dept) => $dept?->getId(),
                // Reverse: ID -> Entity
                reverseTransform: fn($id) => $id ? $departmentRepo->find($id) : null
            ))
            ->add();

        // JSON array transformation
        $form->addTextarea('preferences', 'User Preferences (JSON)')
            ->addTransformer(new CallbackTransformer(
                transform: fn($array) => json_encode($array, JSON_PRETTY_PRINT),
                reverseTransform: fn($json) => json_decode($json, true)
            ))
            ->add();

        // DateTimeImmutable transformation
        $form->addText('createdAt', 'Member Since')
            ->addTransformer(new CallbackTransformer(
                transform: fn($dt) => $dt?->format('F j, Y'),
                reverseTransform: fn($str) => new \DateTimeImmutable($str)
            ))
            ->readonly()
            ->add();

        $form->addSubmit('save', 'Update User');

        return $this->render('user/edit.html.twig', [
            'form' => $form->build(),
            'user' => $user,
        ]);
    }

    /**
     * Process form submission with reverse transformation
     *
     * @Route("/user/update/{id}", name="user_update", methods={"POST"})
     */
    public function updateUser(int $id, Request $request): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Get submitted form data (in view format - strings)
        $submittedData = $request->request->all();

        // Rebuild form to get transformers
        $form = $this->buildUserForm($user);

        // Apply reverse transformation (view -> model)
        // This converts:
        // - '1990-05-15' -> DateTime object
        // - 'ROLE_USER, ROLE_ADMIN' -> ['ROLE_USER', 'ROLE_ADMIN']
        // - '5' -> Department entity
        // - '{"theme":"dark"}' -> ['theme' => 'dark']
        $modelData = $form->applyReverseTransform($submittedData);

        // Now $modelData contains properly typed values ready for entity
        $user->setName($modelData['name']);
        $user->setEmail($modelData['email']);
        $user->setBirthday($modelData['birthday']);           // DateTime object
        $user->setRoles($modelData['roles']);                 // Array
        $user->setDepartment($modelData['department']);       // Entity
        $user->setPreferences($modelData['preferences']);     // Array

        // Validate and save
        $this->entityManager->flush();

        $this->addFlash('success', 'User updated successfully!');

        return $this->redirectToRoute('user_edit', ['id' => $id]);
    }

    /**
     * Example: API endpoint that returns transformed data
     *
     * @Route("/api/user/{id}", name="api_user", methods={"GET"})
     */
    public function apiUser(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        // Build form with transformers
        $form = $this->buildUserForm($user);

        // Get form as JSON (with transformations applied)
        // DateTime objects are converted to strings
        // Entity objects are converted to IDs
        // Arrays are converted to comma-separated strings
        $formJson = $form->buildAsJson();

        return new Response($formJson, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Build user form with all transformers configured
     */
    private function buildUserForm(User $user): FormBuilder
    {
        $userData = [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'birthday' => $user->getBirthday(),
            'roles' => $user->getRoles(),
            'department' => $user->getDepartment(),
            'preferences' => $user->getPreferences(),
            'createdAt' => $user->getCreatedAt(),
        ];

        $form = FormBuilder::create('user_edit_form')
            ->setRenderer($this->formRenderer)
            ->setTheme(new Bootstrap5Theme())
            ->setData($userData);

        $form->addHidden('id', $user->getId())->add();

        $form->addText('name', 'Full Name')
            ->required()
            ->add();

        $form->addEmail('email', 'Email Address')
            ->required()
            ->add();

        $form->addDate('birthday', 'Date of Birth')
            ->addTransformer(new DateTimeToStringTransformer('Y-m-d'))
            ->required()
            ->add();

        $form->addText('roles', 'User Roles')
            ->addTransformer(new StringToArrayTransformer(', '))
            ->add();

        $departmentRepo = $this->entityManager->getRepository(Department::class);
        $departments = $departmentRepo->findAll();
        $departmentOptions = [];
        foreach ($departments as $dept) {
            $departmentOptions[$dept->getId()] = $dept->getName();
        }

        $form->addSelect('department', 'Department')
            ->options($departmentOptions)
            ->addTransformer(new CallbackTransformer(
                transform: fn($dept) => $dept?->getId(),
                reverseTransform: fn($id) => $id ? $departmentRepo->find($id) : null
            ))
            ->add();

        $form->addTextarea('preferences', 'User Preferences (JSON)')
            ->addTransformer(new CallbackTransformer(
                transform: fn($array) => json_encode($array, JSON_PRETTY_PRINT),
                reverseTransform: fn($json) => json_decode($json, true)
            ))
            ->add();

        $form->addText('createdAt', 'Member Since')
            ->addTransformer(new CallbackTransformer(
                transform: fn($dt) => $dt?->format('F j, Y'),
                reverseTransform: fn($str) => new \DateTimeImmutable($str)
            ))
            ->readonly()
            ->add();

        $form->addSubmit('save', 'Update User');

        return $form;
    }
}

// Example Entity classes for reference

class User
{
    private ?int $id = null;
    private string $name;
    private string $email;
    private ?\DateTime $birthday = null;
    private array $roles = [];
    private ?Department $department = null;
    private array $preferences = [];
    private ?\DateTimeImmutable $createdAt = null;

    // Getters and setters...
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function getBirthday(): ?\DateTime { return $this->birthday; }
    public function setBirthday(?\DateTime $birthday): void { $this->birthday = $birthday; }
    public function getRoles(): array { return $this->roles; }
    public function setRoles(array $roles): void { $this->roles = $roles; }
    public function getDepartment(): ?Department { return $this->department; }
    public function setDepartment(?Department $department): void { $this->department = $department; }
    public function getPreferences(): array { return $this->preferences; }
    public function setPreferences(array $preferences): void { $this->preferences = $preferences; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
}

class Department
{
    private ?int $id = null;
    private string $name;

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function setName(string $name): void { $this->name = $name; }
}
