<main class="form-signin w-100 m-auto">
    <form action="/login" method="post">
        <img class="mb-4" src="/assets/images/calendar_31_2x.png" alt="">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>
        <?
        if (isset($error)) {
            echo "<div class=\"alert alert-danger\" role=\"alert\">$error</div>";
        }
        ?>

        <div class="form-floating">
            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email"
                value="<?php echo isset($fields['email']) ? $fields['email'] : ''; ?>">
            <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password"
                value="<?php echo isset($fields['password']) ? $fields['password'] : ''; ?>">
            <label for="floatingPassword">Password</label>
        </div>

        <!-- <div class="form-check text-start my-3">
            <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
            <label class="form-check-label" for="flexCheckDefault">
                Remember me
            </label>
        </div> -->
        <button class="btn btn-primary w-100 py-2" type="submit">Sign in</button>
        <p class="mt-5 mb-3 text-body-secondary">© 2024–2024</p>
    </form>
</main>