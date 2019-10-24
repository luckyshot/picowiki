</div>

<nav id="nav-main" class="nav-main">
    <input type="checkbox" id="nav-checkbox">
    <label for="nav-checkbox" id="nav-toggle">☰</label>
    <ul>
        <?php foreach( $this->file_list as $file ){ ?>
            <li><a href="<?=$this->config['app_url'].$file?>"><?=$file?></a></li>
        <?php } ?>
    </ul>
</nav>

<?=$this->event('template_footer', $this)?>

</body>
</html>
