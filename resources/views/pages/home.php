<div>
    <h1>Homepage</h1>
    <?php
    use app\Application\Session;

    if (Session::get('user')) {
        echo 'You are logged in as ' . Session::get('user')->fullName . ' (' . Session::get('user')->email . ')';
    }
    if (isset($errors)) {
        foreach ($errors as $error) {
            echo "<div class=\"alert alert-danger\" role=\"alert\">$error</div>";
        }
    }
    ?>

</div>