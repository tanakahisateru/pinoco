<?php
echo "<pre>";
/** @var Pinoco_PDOWrapper $db */
$db = $this->db;

try {
    $db->beginTransaction();
    $s = $db->prepare('select * from foo;')->query();
    $foo = $s->fetch();
    while ($foo) {
        printf("foo:%d has %s.\n", $foo->id, $foo->value);
        $foo = $s->fetch();
    }
    $s->closeCursor();
    
    printf("insert ddd affected %d rows\n", $db->prepare("insert into foo (value) values(?)")->execute("ddd"));
    
    foreach ($db->prepare('select * from foo where id<:maxid;')->query(array('maxid'=>10))->fetchAll() as $foo) {
        printf("foo:%d has %s.\n", $foo->id, $foo->value);
    }
    
    printf("update xxx affected %d rows\n", $db->execute("update foo set value='xxx' where id<3"));
    printf("update yyy affected %d rows\n", $db->prepare("update foo set value=? where id<?")->execute('yyy', 2));
    
    foreach ($db->query('select * from foo;')->fetchAll() as $foo) {
        printf("foo:%d has %s.\n", $foo->id, $foo->value);
    }
    
    $db->execute("select * from non_existence"); // throws error absolutely
    
    $db->commit(); // never executed
}
catch (PDOException $ex) {
    $db->rollBack();
    echo $ex->getMessage() . "\n";
}
foreach ($db->query('select * from foo;')->fetchAll() as $foo) {
    printf("foo:%d has %s.\n", $foo->id, $foo->value);
}
echo "</pre>";

$this->page = null;
