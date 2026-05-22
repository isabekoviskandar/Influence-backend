<!DOCTYPE html>
<html>
<head>
    <title>Welcome Email</title>
</head>
<body>
    <h1>Welcome, {{ $user->username }}!</h1>
    <p>Your OTP is {{ $otp }}.</p>
    <p>This code expires in 10 minutes.</p>
</body>
</html>
