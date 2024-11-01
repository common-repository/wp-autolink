<?php global $t_autolink;?>
<div class="wrap">
  <div class="icon32" id="icon-wp-autolink"><br/></div>
  <h2>Auto Link <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=new" class="add-new-h2"><?php echo __('Add New Keyword','wp-autolink'); ?></a> </h2>


<?php 
$saction = $_REQUEST['saction'];
switch($saction){
 case 'new':
 case 'edit':
?>
<script type="text/javascript">
function addNew(){
  if(document.getElementById("Keyword").value=='' || document.getElementById("Link").value==''){
	 alert("<?php echo __('Please enter both a keyword and URL','wp-autolink'); ?>");
	 return;
  }
  document.getElementById("myform").submit();
}
</script>

<form id="myform"  method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php" > 
<?php
if($saction=='edit'){
  $autolink = $wpdb->get_row('SELECT * FROM '.$t_autolink.' WHERE id = '.$_REQUEST['id'] );
  list($Link,$Description,$NoFollow,$NewWindow,$FirstMatchOnly,$Ignorecase,$WholeWord) = explode("|",$autolink->details);
?>
<input type="hidden" name="tid" id="tid" value="<?php echo $_REQUEST['id']; ?>">
<input type="hidden" name="saction" id="saction" value="saveKeyword">
<?php }else{ ?>
<input type="hidden" name="saction" id="saction" value="newKeyword">
 <?php } ?> 
  <br/> 
  <table> 	   
       <tbody id="the-list">         	  
       <tr> 
		 <td width="10%"><?php echo __('Keyword','wp-autolink'); ?>:</td>
		 <td><input type="text" name="Keyword" id="Keyword" value="<?php echo $autolink->keyword; ?>"> * </td>
	   </tr>
	   <tr> 
		 <td width="10%"><?php echo __('Link','wp-autolink'); ?>:</td>
		 <td><input type="text" name="Link" id="Link" value="<?php echo $Link; ?>" size="100"> * </td>
	   </tr>
	   <tr> 
		 <td width="10%"><?php echo __('Description','wp-autolink'); ?>:</td>
		 <td><input type="text" name="Description" id="Description" value="<?php echo $Description; ?>" size="50"></td>
	   </tr>
       <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="NoFollow"  value="1" <?php if($NoFollow==1)echo 'checked'; ?> /> <?php echo __('No Follow','wp-autolink'); ?> <a title='<?php echo __('This adds a rel= "nofollow" to the link.','wp-autolink'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="NewWindow"  value="1" <?php if($NewWindow==1)echo 'checked'; ?> /> <?php echo __('New Window','wp-autolink'); ?> <a title='<?php echo __('This adds a target="_blank" to the link, forcing a new browser window on clicking.','wp-autolink'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="FirstMatchOnly"  value="1" <?php if($FirstMatchOnly==1)echo 'checked'; ?> /> <?php echo __('First Match Only','wp-autolink'); ?> <a title='<?php echo __('Only add links on the first matched.','wp-autolink'); ?>'>[?]</a>
		 </td>
	   </tr>
	   <tr>
	     <td></td>
         <td>  
		    <input type="checkbox" name="Ignorecase"  value="1" <?php if($Ignorecase==1)echo 'checked'; ?> /> <?php echo __('Ignore Case','wp-autolink'); ?>
		 </td>
	   </tr>  
       <tr>
	     <td></td>
         <td>
		 <?php if($saction=='edit'){ ?>
		    <input type="checkbox" name="WholeWord"  value="1" <?php if($WholeWord==1)echo 'checked'; ?> /> <?php echo __('Match Whole Word','wp-autolink'); ?> 
		 <?php }else{ ?>
            <input type="checkbox" name="WholeWord"  value="1" <?php if(!(get_bloginfo('language')=='zh-CN'))echo 'checked'; ?> /> <?php echo __('Match Whole Word','wp-autolink'); ?>
		 <?php } ?>
         <?php if((get_bloginfo('language')=='zh-CN'))echo '(中文请勿勾选)'; ?>
		 <a title='<?php echo __('Match whole word only. For language split by "space", like English or other Latin languages.','wp-autolink'); ?>'>[?]</a>
		 </td>
	   </tr> 
	   </tbody>
  </table>
  <p class="submit"><input type="button" class="button-primary" value="<?php echo __('Submit'); ?>"  onclick="addNew()"/> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php<?php if($_REQUEST['p']!=null) echo '&p='.$_REQUEST['p']; ?>" class="button"><?php echo __('Return','wp-autolink'); ?></a></p>
</form>


<?php
 break; // end case 'edit':
 case 'auto_link':
 
 $n = $_GET['n'];
 $pageNum=20;
 
 $autolinks = $wpdb->get_results('SELECT * FROM '.$t_autolink);

 // Get objects
 $objects = (array) $wpdb->get_results( $wpdb->prepare("SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' ORDER BY ID DESC LIMIT %d, %d", $n,$pageNum) );
 
 if( !empty($objects) ) {
	echo '<ul>';
	foreach( $objects as $object ) {
	  autoLinkPost($object,$autolinks);						    
	  echo '<li>#'. $object->ID .' '. $object->post_title .'</li>';
	  unset($object);
	}
	echo '</ul>';
?>
	<p><?php _e("If your browser doesn't start loading the next page automatically click this link:", 'wp-autolink'); ?> <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=auto_link&n=<?php echo $n + $pageNum; ?>"><?php _e('Next content', 'wp-autolink'); ?></a></p>
	<script type="text/javascript">
	// <![CDATA[
	function nextPage() {
	  location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=auto_link&n=<?php echo $n + $pageNum; ?>";
	}
	window.setTimeout( 'nextPage()', 300 );
	// ]]>
	</script>
<?php
 } else {
	echo '<p><strong>All done! </strong></p>';
 }
?>
  

<?php
 break;
 
 case 'newKeyword':
 case 'saveKeyword':
 case 'BatchDelete':
 case 'delete':	 
 default:
?>

<?php
if($saction=='newKeyword'){
  $Keyword = $_POST['Keyword'];
  $Link = $_POST['Link'];
  $Description = ($_POST['Description']=='')?$Keyword:$_POST['Description'];
  $NoFollow = $_POST["NoFollow"]?$_POST["NoFollow"]:0;
  $NewWindow = $_POST["NewWindow"]?$_POST["NewWindow"]:0;
  $FirstMatchOnly = $_POST["FirstMatchOnly"]?$_POST["FirstMatchOnly"]:0;
  $Ignorecase = $_POST["Ignorecase"]?$_POST["Ignorecase"]:0;
  $WholeWord = $_POST["WholeWord"]?$_POST["WholeWord"]:0;
  
  $details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

  $wpdb->query("INSERT INTO $t_autolink(keyword,details) VALUES ( '$Keyword','$details')");
  
  echo '<div id="message" class="updated fade"><p>'.__('A new keyword has been created.','wp-autolink').'</p></div>';
}

if($saction=='saveKeyword'){
  $Keyword = $_POST['Keyword'];
  $Link = $_POST['Link'];
  $Description = ($_POST['Description']=='')?$Keyword:$_POST['Description'];
  $NoFollow = $_POST["NoFollow"]?$_POST["NoFollow"]:0;
  $NewWindow = $_POST["NewWindow"]?$_POST["NewWindow"]:0;
  $FirstMatchOnly = $_POST["FirstMatchOnly"]?$_POST["FirstMatchOnly"]:0;
  $Ignorecase = $_POST["Ignorecase"]?$_POST["Ignorecase"]:0;
  $WholeWord = $_POST["WholeWord"]?$_POST["WholeWord"]:0;
  
  $details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

  $wpdb->query("UPDATE $t_autolink SET keyword = '$Keyword', details='$details' WHERE id = ".$_POST['tid'] );
  
   echo '<div id="message" class="updated fade"><p>'.__('Updated!','wp-autolink').'</p></div>';
}

if($saction=='delete'){
  $wpdb->query("DELETE FROM $t_autolink WHERE id = ".$_REQUEST['id'] );
  
  echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autolink').'</p></div>';
}
if($saction=='BatchDelete'){
   $ids = $_POST['ids']; 
   if($ids!=null)
   foreach($ids as $id){
     $wpdb->query("DELETE FROM $t_autolink WHERE id = ".$id );
   }
   echo '<div id="message" class="updated fade"><p>'.__('Deleted!','wp-autolink').'</p></div>';
}
?>

<p><?php echo __('Auto Link can automatically add links on keywords when publish post.','wp-autolink'); ?></p>
<form id="myform"  method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php" >
  <input type="hidden" name="saction" id="saction" value="">
  
  <table class="widefat plugins"  style="margin-top:4px"> 
	<thead>
	  <tr>
	    <th style="text-align:left" width="28"><input type="checkbox" name="All" onclick="checkAll('ids[]')"></th>
	    <th scope="col" style="text-align:left" ><?php echo __('Keyword','wp-autolink'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Link','wp-autolink'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Description','wp-autolink'); ?></th>
		<th scope="col" style="text-align:left" ><?php echo __('Attributes','wp-autolink'); ?></th>
		<th scope="col" style="text-align:center" width="4%"></th>
	  </tr>
	</thead>   
    <tbody id="the-list">         
<?php
$perPage=7;
$total = $wpdb->get_var('SELECT count(*) FROM '.$t_autolink);
$total_pages = ceil($total / $perPage);	  

if(!isset($_REQUEST['p'])){ 
  $page = 1; 
} else { 
  $page = $_REQUEST['p']; 
}

if($saction=='newKeyword')$page = $total_pages;
if($page>$total_pages)$page = $total_pages;

// Figure out the limit for the query based on the current page number. 
$from = (($page * $perPage) - $perPage);

$autoLinks = $wpdb->get_results('SELECT * FROM '.$t_autolink.' ORDER BY id LIMIT '.$from.','.$perPage); 
?>
<?php 
foreach ($autoLinks as $autoLink) {
	
	$details=$Link.'|'.$Description.'|'.$NoFollow.'|'.$NewWindow.'|'.$FirstMatchOnly.'|'.$Ignorecase.'|'.$WholeWord;

    list($Link,$Description,$NoFollow,$NewWindow,$FirstMatchOnly,$Ignorecase,$WholeWord) = explode("|",$autoLink->details);
?>
     <tr>
	   <td style="text-align:center"><input type="checkbox" name="ids[]" value="<?php echo $autoLink->id; ?>" /></td>
	   <td>
	     <a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=edit&id=<?php echo $autoLink->id; ?>&p=<?php echo $page; ?>" >
	      <?php echo $autoLink->keyword; ?>
		 </a>
	   </td>
	   <td> 
	     <a href="<?php echo $Link; ?>" target="_blank">
	      <?php echo $Link; ?>
		 </a>
	   </td>
	   <td> 
	      <?php echo $Description; ?>
	   </td>
	   <td>
	      <?php if($NoFollow==1){?>[<code><?php echo __('No Follow','wp-autolink'); ?></code>]<?php } ?>
		  <?php if($NewWindow==1){?>[<code><?php echo __('New Window','wp-autolink'); ?></code>]<?php } ?>
		  <?php if($FirstMatchOnly==1){?>[<code><?php echo __('First Match Only','wp-autolink'); ?></code>]<?php } ?>
		  <?php if($Ignorecase==1){?>[<code><?php echo __('Ignore Case','wp-autolink'); ?></code>]<?php } ?>
		  <?php if($WholeWord==1){?>[<code><?php echo __('Match Whole Word','wp-autolink'); ?></code>]<?php } ?>    
	   </td>
	   <td> 
	     <span class="trash"><a href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=delete&id=<?php echo $autoLink->id; ?>&p=<?php echo $page; ?>" ><?php echo __('Delete'); ?></a></span>
	   </td>
 	 </tr>
<?php } ?>
    </tbody>
	<tfoot>
	   <tr style="text-align:center">  
		  <td colspan="6" style="text-align:left">
		  <input type="button" class="button-primary" value=" <?php echo __('Batch Delete','wp-autolink'); ?> "  onclick="BatchDelete()"/>
		  </td>
		</tr>    
    </tfoot>
  </table>
   <div class="tablenav">
      <div class="tablenav-pages alignright">
	   <?php
			// $total_pages=3;
		    // $page = 2;
					if ($total_pages>1) {						
						$arr_params = array (
						  'page' => 'wp-autolink/wp-autolink-list.php',  
						  'p' => "%#%"
						);
						$query_page = add_query_arg( $arr_params , $query_page );				
						echo paginate_links( array(
							'base' => $query_page,
							'prev_text' => __('&laquo; Previous'),
							'next_text' => __('Next &raquo;'),
							'total' => $total_pages,
							'current' => $page,
							'end_size' => 1,
							'mid_size' => 5,
						));
					}
		?>	
       </div> 
	</div>
  </form>
  <h3><?php echo __('Auto links old content','wp-autolink'); ?></h3>
  <p><?php echo __('Auto Link can also add keyword links all existing contents of your blog.','wp-autolink'); ?> <?php echo __('This feature use keyword list above-mentioned.','wp-autolink'); ?></p>
  <a class="button-primary" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=wp-autolink/wp-autolink-list.php&saction=auto_link&n=0"><?php echo __('Auto links all old content &raquo;','wp-autolink'); ?></a>

<script type="text/javascript">
function BatchDelete(){
  document.getElementById("saction").value="BatchDelete";
  document.getElementById("myform").submit();
}

		
function checkAll(str){   
  var a = document.getElementsByName(str);   
  var n = a.length;   
  for (var i=0; i<n; i++) a[i].checked = window.event.srcElement.checked;   
}
</script>
<?php 
  break;
}// end switch($saction){
?>
</div>