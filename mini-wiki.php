<?php require("parsing.inc.php"); ?>
<?php
$wiki_file = dirname(__FILE__).'/wiki.txt';
// SALVA
if(isset($_POST["wiki"]))
{
	$fp = fopen($wiki_file,"w") or die(__("Error opening file in write mode.", "wiki-dashboard"));	
	fwrite($fp,$_POST["wiki"]);

	fclose($fp);
}
?>
	<div class="wrap">
 <h2><?php _e("Wiki"); ?></h2>  <div id="wiki" style="width: 100%;" >
<?php
// Gestione operazioni

// STAMPA
if(!isset($_GET["op"]))
{
	$fp = fopen($wiki_file,"r") or die(__("Error opening file in read mode.", "wiki-dashboard"));
	
	$parsed = "";
	
	while ($data = fread($fp, 4096))
	{
		$parsed .= $data;
	}

	fclose($fp);

	parsing($parsed);
	
	print($parsed);
}

//MODIFICA
if(isset($_GET["op"]) && ($_GET["op"] == "edit"))
{
	?>
	<div align="center">
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=wiki" method="post" id="modulo_wiki">
	<textarea name="wiki" style="width:99%; height:300px;" text=""><?php
	// Legge file e stampa contenuto nella textarea.
	$fp = fopen($wiki_file,"r") or die(__("Error opening file in read mode.", "wiki-dashboard"));
	while ($data = fread($fp, 4096))
	{
		print($data);
	}
	fclose($fp);
	?></textarea>
	<br />
	<input name="ok" type="submit" value= "<?php _e("Save", "wiki-dashboard");?>"  />&nbsp;<input name="ko" type="reset" value= "<?php _e("Restore last version", "wiki-dashboard");?>" />
	</form>
	</div>
	<?php
}
?>
  </div>
<hr />
<?php 
if ( (!isset($_GET["op"])) || (!((isset($_GET["op"])) && ($_GET["op"] == "edit"))) )
{ 
	?>
	<p><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wiki&op=edit"><?php _e("Edit page", "wiki-dashboard");?></a></p> 
<?php 
} 
?>
</div>
