        <h2>URL conversion</h2>
        <ul>
            <li>url: subpage.html = <?php echo $this->url("subpage.html"); ?></li>
            <li>url: subdir/index.html = <?php echo $this->url("subdir/index.html"); ?></li>
            <li>url: /abspath/index.html = <?php echo $this->url("/abspath/index.html"); ?></li>
        </ul>
        
        <h2>Available variables of $this</h2>
        <ul>
        <?php foreach($this as $name=>$value): ?>
            <li>$this-&gt;<?php echo $name; ?> = <?php echo $value; ?></li>
        <?php endforeach; ?>
        </ul>

