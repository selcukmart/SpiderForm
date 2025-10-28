{{-- Example: Contact Form using Blade Components --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2>Contact Us</h2>

                {{-- Using Blade Components --}}
                <x-form name="contact" action="/contact" method="POST">

                    <x-form-text
                        name="full_name"
                        label="Full Name"
                        required
                        placeholder="Enter your full name"
                        help="Your first and last name"
                    />

                    <x-form-email
                        name="email"
                        label="Email Address"
                        required
                        placeholder="your@email.com"
                    />

                    <x-form-text
                        name="subject"
                        label="Subject"
                        required
                        placeholder="What is this about?"
                    />

                    <x-form-textarea
                        name="message"
                        label="Message"
                        required
                        rows="8"
                        placeholder="Type your message here..."
                        help="Please provide as much detail as possible"
                    />

                    <x-form-select
                        name="department"
                        label="Department"
                        :options="[
                            'sales' => 'Sales',
                            'support' => 'Technical Support',
                            'billing' => 'Billing',
                            'general' => 'General Inquiry'
                        ]"
                        required
                    />

                    <x-form-checkbox
                        name="newsletter"
                        label="Subscribe to our newsletter"
                    />

                    <x-form-submit>Send Message</x-form-submit>

                </x-form>
            </div>
        </div>
    </div>
</body>
</html>
