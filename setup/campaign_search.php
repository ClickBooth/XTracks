<?php

include_once($_SERVER['DOCUMENT_ROOT'] . '/xtracks-app/bootstrap.php');

$query = $_GET['query'];
$s_query = db::escape($query);
$parts = explode('.', $_SERVER['SERVER_NAME']);
$subdomain = $parts[0];


$rows = db::getRows("SELECT distinct c.name, c.id
                    FROM prosper_master.campaigns c
                    LEFT JOIN prosper_master.campaign_permissions cp
                    ON cp.campaign_id = c.id
                    LEFT JOIN prosper_master.installs i
                    ON i.affiliate_id = cp.account_id
                    AND i.subdomain = '$subdomain'
                     WHERE c.name LIKE '%{$s_query}%'
                        AND c.status='active'
                        AND c.hidden=0
                        AND (c.target = 'public' OR cp.allow_deny = 'allow')
                    LIMIT 10");

foreach($rows as $r) {
    $suggestions[] = $r['id'].' - '.$r['name'];
    $data[] = $r['id'];
}

$data = array(
    'query'=>$query,
    'suggestions'=>$suggestions,
    'data'=>$data
);

echo json_encode($data);