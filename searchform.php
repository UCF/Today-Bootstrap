<div class="search">
	<form role="search" method="get" class="search-form" action="<?=home_url( '/' )?>">
		<label for="s">Search:</label>
		<input type="text" value="<?=htmlentities($_GET['s'])?>" name="s" class="search-field" id="s" placeholder="Search UCF Today" />
		<button type="submit" class="search-submit"><span class="sr-only">Search</span><span class="fa fa-search" aria-hidden="true"></span></button>
	</form>
</div>
