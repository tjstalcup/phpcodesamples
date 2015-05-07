<?php
	$nids = db_select('node', 'n')
	    ->fields('n', array('nid'))
	    ->fields('n', array('type'))
	    ->condition('n.type', 'status')
	    ->execute()
	    ->fetchCol(); // returns an indexed array
	// Now return the node objects.
	$ns = node_load_multiple($nids);
	$nodes = array();
	foreach ($ns as $n) {
		$nodes[$n->field_date['und'][0][value]."-".str_replace(":", "", $n->field_time['und'][0][value]).$n->nid] = $n;
	}
	krsort($nodes);
?>

<?php foreach($nodes as $n): ?>
<div class='statusBlock'>
	<h2><?=date("M d, Y", strtotime($n->field_date['und'][0][value]))?></h2>	
	<?= showStatus($n); ?>
</div>
<?php endforeach; ?>

<?php
function showStatus($n){
	$html = "<div class='row statusUpdate'>";
	$status = strtolower($n->field_statusicon['und'][0][value]);
	$html .= "<div class='col-sm-2 statusIcon ".$status."'>";
	switch ($status) {
		case 'green':
			$html .= "<i class='fa fa-check-circle'></i>";
			break;
		case 'blue':
			$html .= "<i class='fa fa-info-circle'></i>";
			break;
		case 'red':
			$html .= "<i class='fa fa-times-circle'></i>";
			break;
		case 'yellow':
			$html .= "<i class='fa fa-warning'></i>";
			break;
	}
	$html .= "</div><div class='col-sm-10 statusBody'>";
	$html .= "<h3>".$n->field_time['und'][0][value]." EST</h3>";
	$html .= "<p>".$n->body['und'][0][value]."</p>";
	$html .= "</div></div>";
	return $html;
}
?>