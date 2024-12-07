<main class="form-signin w-100 m-auto">
    <link rel="stylesheet" href="/assets/css/login.css">
    <form action="/login" method="post">
        <img id="logo" class="mb-4" src="/assets/images/Logo.svg" alt="Logo">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
        <?php
        if (isset($error)) {
            echo "<div class=\"alert alert-danger\" role=\"alert\">$error</div>";
        }
        ?>

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

        <!-- <div class="form-check text-start my-3">
            <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
            <label class="form-check-label" for="flexCheckDefault">
                Remember me
            </label>
        </div> -->
        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
        <p class="my-3 text-body-secondary">Don't have an account? <a href="/register">Sign up</a></p>
        <p class="mb-3 text-body-secondary">&copy; 2024&hyphen;2024</p>
    </form>
</main>