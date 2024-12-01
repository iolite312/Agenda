<div>
    <h1>Homepage</h1>
    <?
    use app\Application\Session;
    if (Session::get('user')) {
        echo "You are logged in as " . Session::get('user')->fullName;
        echo '<form action="/logout" method="post">
                <button type="submit">Logout</button>
            </form>';
    }
    ?>

</div>