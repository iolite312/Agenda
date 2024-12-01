<div>
    <h1 class="text-2xl font-medium">Login</h1>
    <?
    if (isset($error)) {
        echo "<p>$error</p>";
    }
    ?>
    <form action="/login" method="post" class="">
        <div class="row mb-3">
            <label for="inputEmail3" class="col-sm-2 col-form-label">Email</label>
            <div class="col-sm-10">
                <input type="email" class="form-control" id="inputEmail3">
            </div>
        </div>
        <div class="row mb-3">
            <label for="inputPassword3" class="col-sm-2 col-form-label">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="inputPassword3">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Sign in</button>
    </form>
</div>