<div>
    <h1>Hi</h1>
    <?
    foreach ($databases as $database) {
        echo "<p>{$database->Database}</p>";
    }
    var_dump($_SESSION);
    ?>
</div>