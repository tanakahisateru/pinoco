<?php
$this->db = $this->newobj('PDOWrapper.php/PDOWrapper', 'sqlite::memory:');

$schema = <<<EOT
create table foo (
    id integer primary key autoincrement,
    value varchar
);
insert into foo (value) values('aaa');
insert into foo (value) values('bbb');
insert into foo (value) values('ccc');
EOT;
foreach(explode(';', $schema) as $sql) {
    $sql = trim($sql);
    if($sql) {
        $this->db->exec($sql);
    }
}

