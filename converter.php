<?php

if (!file_exists(dirname(__FILE__) . '/SSI.php'))
	die('Cannot find SSI.php');
require_once(dirname(__FILE__) . '/SSI.php');

$request = $smcFunc['db_query']('', '
	SELECT title, content, views, permissions
	FROM {db_prefix}ezp_page',
	array(
	)
);
$pages = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
{
	$namespace = strtolower(preg_replace('~[^A-Za-z0-9_]+~', '', $row['title']));
	if (empty($namespace))
		$namespace = 'page' . mt_rand(1, 5000);
	if (isset($pages[$namespace]))
		$namespace .= '2';

	$pages[$namespace] = array(
		'namespace' => $namespace,
		'title' => $smcFunc['htmlspecialchars']($row['title'], ENT_QUOTES),
		'body' => $smcFunc['htmlspecialchars'](html_entity_decode($row['content']), ENT_QUOTES),
		'type' => 'html',
		'permission_set' => 0,
		'groups_allowed' => $row['permissions'],
		'groups_denied' => '',
		'style' => '',
		'status' => 1,
	);
}
$smcFunc['db_free_result']($request);

$request = $smcFunc['db_query']('', '
	SELECT
		ebl.id_layout, ebl.id_column, ebl.customtitle, eb.blocktype,
		eb.blockdata, ebl.permissions, ebl.can_collapse, ebl.active, ebl.hidetitlebar
	FROM {db_prefix}ezp_block_layout AS ebl
		INNER JOIN {db_prefix}ezp_blocks AS eb ON (eb.id_block = ebl.id_block)
	ORDER BY ebl.id_order',
	array(
	)
);
$blocks = array();
$rows = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
{
	$type = '';
	$column = 0;

	switch ($row['blockdata'])
	{
		case null:
			$type = $row['blocktype'] == 'HTML' ? 'sp_html' : 'sp_php';
			break;
		case 'EzBlockBoardNewsBlock':
			$type = 'sp_boardNews';
			break;
		case 'EzBlockLoginBoxBlock':
			$type = 'sp_userInfo';
			break;
		case 'EzBlockPollBlock':
			$type = 'sp_showPoll';
			break;
		case 'EzBlockRecentPostsBlock':
		case 'EzBlockRecentTopicsBlock':
			$type = 'sp_recent';
			break;
		case 'EzBlockSearchBlock':
			$type = 'sp_quickSearch';
			break;
		case 'EzBlockThemeSelect':
			$type = 'sp_theme_select';
			break;
		case 'EzBlockWhoIsOnline':
			$type = 'sp_whosOnline';
			break;
		case 'EzBlockSMFArcadeBlock':
			$type = 'sp_arcade';
			break;
		case 'EzBlockGalleryBlock':
			$type = 'sp_gallery';
			break;
		case 'EzBlockGalleryBlock':
		case 'EzBlockGalleryRandomImage':
			$type = 'sp_gallery';
			break;
		case 'EzBlockRecentMembersBlock':
			$type = 'sp_latestMember';
			break;
		case 'EzBlockTopPosterBlock':
			$type = 'sp_topPoster';
			break;
		case 'EzBlockRSSBlock':
			$type = 'sp_rssFeed';
			break;
		case 'EzBlockParseBBCBlock':
			$type = 'sp_bbc';
			break;
		case 'EzBlockStatsBox':
			$type = 'sp_boardStats';
			break;
		case 'EzBlockTopTopicsBlock':
			$type = 'sp_topTopics';
			break;
		case 'EzBlockTopBoards':
			$type = 'sp_topBoards';
			break;
		case 'EzBlockBirthDaysBlock':
			$type = 'sp_calendarInformation';
			break;
		default:
			break;
	}

	if (empty($type))
		continue;

	switch ($row['id_column'])
	{
		case 1:
			$column = 1;
			break;
		case 2:
			$column = 2;
			break;
		case 3:
			$column = 4;
			break;
		case 4:
			$column = 5;
			break;
		case 5:
			$column = 6;
			break;
		default:
			$column = 6;
			break;
	}

	$blocks[$row['id_layout']] = array(
		'label' => $smcFunc['htmlspecialchars']($row['customtitle'], ENT_QUOTES),
		'type' => $type,
		'col' => $column,
		'row' => !isset($rows[$column]) ? ($rows[$column] = 1) : ++$rows[$column],
		'permission_set' => 0,
		'groups_allowed' => $row['permissions'],
		'groups_denied' => '',
		'state' => !empty($row['active']) ? 1 : 0,
		'force_view' => !empty($row['can_collapse']) ? 0 : 1,
		'display' => '',
		'display_custom' => '',
		'style' => !empty($row['hidetitlebar']) ? 'title_default_class~|title_custom_class~|title_custom_style~|body_default_class~windowbg|body_custom_class~|body_custom_style~|no_title~1|no_body~' : '',
	);
}
$smcFunc['db_free_result']($request);

echo '<pre>';
print_r($pages);
print_r($blocks);

?>