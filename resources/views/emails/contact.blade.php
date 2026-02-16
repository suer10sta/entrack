<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New contact message</title>
</head>
<body>
    <h2>New message from the contact form</h2>

    <p><strong>Name:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Subject:</strong> {{ $subjectLine }}</p>

    <hr>

    <p><strong>Message:</strong></p>
    <p style="white-space: pre-line;">
        {{ $messageBody }}
    </p>
</body>
</html>


