<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>
  <!-- show and hide the password -->
  <script>
    let passwordInput = document.getElementById('password');

    document.querySelector('.hide').addEventListener('click', function() {
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
      } else {
        passwordInput.type = 'password';
      }
    });

    // validate the length of the password and the password contain characters , numbers and special characters
    function validatePassword() {
      const password = document.getElementById('password').value;
      const length = password.length;
      if (length < 8) {
        alert('Password must be at least 8 characters long.');
        return false;
      }
      const hasNumber = /\d/.test(password);
      if (!hasNumber) {
        alert('Password must contain at least one number.');
        return false;
      }
      const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
      if (!hasSpecialChar) {
        alert('Password must contain at least one special character.');
        return false;
      }
      return true;
    }
  </script>
</body>

</html>