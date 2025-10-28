{{-- Example: User Registration Form using Blade Directives --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>User Registration</h2>

                {{-- Using Blade Directives --}}
                @formStart('register', ['action' => '/register', 'method' => 'POST'])

                @formText('username', 'Username', [
                    'required' => true,
                    'minLength' => 3,
                    'maxLength' => 20,
                    'placeholder' => 'Enter username',
                    'help' => 'Username must be 3-20 characters'
                ])

                @formEmail('email', 'Email Address', [
                    'required' => true,
                    'placeholder' => 'Enter email'
                ])

                @formPassword('password', 'Password', [
                    'required' => true,
                    'minLength' => 8,
                    'placeholder' => 'Enter password',
                    'help' => 'Password must be at least 8 characters'
                ])

                @formPassword('password_confirmation', 'Confirm Password', [
                    'required' => true,
                    'placeholder' => 'Confirm password'
                ])

                @formNumber('age', 'Age', [
                    'required' => true,
                    'min' => 18,
                    'max' => 100
                ])

                @formSelect('country', 'Country', [
                    'us' => 'United States',
                    'uk' => 'United Kingdom',
                    'ca' => 'Canada',
                    'au' => 'Australia'
                ], ['required' => true])

                @formCheckbox('terms', 'I agree to the Terms and Conditions', [
                    'required' => true
                ])

                @formSubmit('Register')

                @formEnd
            </div>
        </div>
    </div>
</body>
</html>
