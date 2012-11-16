<?php
$total_bandwidth = LS_GraphTotalBandwidth();

echo '<img src="'.$body->url($total_bandwidth).'" /><br />';

$top10download = LS_GraphTop10('Top 10 Downloaders (MB/min)', 'bytes', 'in');
echo '<img src="'.$body->url($top10download).'" /><br />';

$top10upload = LS_GraphTop10('Top 10 Uploaders (MB/min)', 'bytes', 'out');
echo '<img src="'.$body->url($top10upload).'" /><br />';

$top10packetsdown = LS_GraphTop10('Top 10 Packets In (per min)', 'packets', 'in');
echo '<img src="'.$body->url($top10packetsdown).'" /><br />';

$top10packetsup = LS_GraphTop10('Top 10 Packets Out (per min)', 'packets', 'out');
echo '<img src="'.$body->url($top10packetsup).'" /><br />';
?>