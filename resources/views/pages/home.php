<div>
    <h1>Homepage</h1>
    <?
    use app\Application\Session;
    if (Session::get('user')) {
        echo "You are logged in as " . Session::get('user')->fullName . " (" . Session::get('user')->email . ")";
    }
    ?>

</div>