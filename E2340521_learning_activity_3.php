<!DOCTYPE html>
<html>
<head>
    <title>PHP Expressions</title>
</head>
<body>
    <h2>My PHP Expressions</h2>
    <?php
    // Given input variables
    $u = 36;
    $v = 89;
    $w = 29;
    $x = 10;
    $y = 7;
    $z = 13;

    // Calculating expressions
    $expr1 = $x + $y * 3; 
    $expr2 = ($u / $v) % $z;
    $expr3 = $y ** $z;  
    $expr4 = ($x == 10) ? 1 : 0;
    $expr5 = ($u > $y) ? 1 : 0;
    $expr6 = $x - $y;
    $expr7 = $w++;
    $expr8 = ++$w;

    // Display results
    echo "<p>u = $u</p>";
    echo "<p>v = $v</p>";
    echo "<p>w = $w</p>";
    echo "<p>x = $x</p>";
    echo "<p>y = $y</p>";
    echo "<p>z = $z</p>";
    
    echo "<ol>";
    echo "<li>x + y * 3 => $expr1</li>";
    echo "<li>(u/v) % z => $expr2</li>";
    echo "<li>y ** z => $expr3</li>";
    echo "<li>Is x equals 10? => $expr4</li>";
    echo "<li>Is u greater than y? => $expr5</li>";
    echo "<li>x = x - y => $expr6</li>";
    echo "<li>w++ => $expr7</li>";
    echo "<li>++w => $expr8</li>";
    echo "</ol>";
    ?>
</body>
</html>
