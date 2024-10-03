/**
 * pirschAnalytics
 *
 * Pirsch Analytics server side integration plugin
 *
 * @author Nicola Lambathakis http://www.tattoocms.it/
 * @category    plugin
 * @version    1.3
 * @license	 http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnWebPageComplete,OnPageNotFound 
 * @internal    @installset base
 * @internal    @modx_category SEO
 * @internal    @properties &pirsch_access_key= Pirsch Access Key:;string; &exclude_docs=Exclude Documents by id (comma separated);string; &exclude_templates=Exclude Templates by id (comma separated);string; &pirsch_tags=Tags:;string;
 * @internal    @disabled 1
 * @lastupdate  03-10-2024
 * @documentation Requirements: This plugin requires Evolution 1.4 or later
 * @documentation https://github.com/Nicola1971/pirschAnalytics/
 * @reportissues https://github.com/Nicola1971/pirschAnalytics/issues
 */

$exclude_docs = explode(',',$exclude_docs);
$exclude_templates = explode(',',$exclude_templates);
if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    $eventName = $modx->event->name;
    switch ($eventName) {
        case 'OnWebPageComplete':
			global $modx;
			$doc_id = $modx -> documentObject['id'];
            $template_id = $modx -> documentObject['template'];
	if (!in_array($doc_id,$exclude_docs) && !in_array($template_id,$exclude_templates)) {
            include_once MODX_BASE_PATH . 'assets/plugins/pirschAnalytics/pirschAnalytics.php';
            break;
    }
			}
}