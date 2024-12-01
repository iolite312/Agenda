<main class="form-signin w-100 m-auto">
    <link rel="stylesheet" href="/assets/css/register.css">
    <form action="/register" method="post">
        <img class="mb-4" src="/assets/images/calendar_31_2x.png" alt="">
        <h1 class="h3 mb-3 fw-normal">Register</h1>
        <?php
        if (isset($error)) {
            echo "<div class=\"alert alert-danger\" role=\"alert\">$error</div>";
        }
        ?>

        <div class="form-floating">
            <input type="text" class="form-control" id="firstNameInput" placeholder="Jhon" name="firstName"
                value="<?php echo $fields['firstName'] ?? ''; ?>">
            <label for="firstNameInput">Firstname</label>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control" id="lastNameInput" placeholder="Doe" name="lastName"
                value="<?php echo $fields['lastName'] ?? ''; ?>">
            <label for="lastNameInput">Lastname</label>
        </div>
        <div class="form-floating">
            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email"
                value="<?php echo $fields['email'] ?? ''; ?>">
            <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password"
                value="<?php echo $fields['password'] ?? ''; ?>">
            <label for="floatingPassword">Password</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingConfirmPassword" placeholder="confirmPassword"
                name="confirmPassword"
                value="<?php echo $fields['confirmPassword'] ?? ''; ?>">
            <label for="floatingPassword">Confirm password</label>
        </div>

        <button class="btn btn-primary w-100 py-2" type="submit">Register</button>
        <p class="my-3 text-body-secondary">Already have an account? <a href="/login">Sign in</a></p>
        <p class="mb-3 text-body-secondary">&copy; 2024&hyphen;2024</p>
    </form>
</main>