/**
 * pirschAnalytics
 *
 * Pirsch Analytics server side integration plugin
 *
 * @author Nicola Lambathakis http://www.tattoocms.it/
 * @category    plugin
 * @version    1.1
 * @license	 http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @events OnWebPageInit
 * @internal    @installset base
 * @internal    @modx_category SEO
 * @internal    @properties &pirsch_access_key= Pirsch Access Key:;string;
 * @internal    @disabled 0
 * @lastupdate  27-09-2024
 * @documentation Requirements: This plugin requires Evolution 1.4 or later
 * @documentation https://github.com/Nicola1971/pirschAnalytics/
 * @reportissues https://github.com/Nicola1971/pirschAnalytics/issues
 */

if (!defined('IN_MANAGER_MODE') || IN_MANAGER_MODE !== true) {
    $eventName = $modx->event->name;
    switch ($eventName) {
        case 'OnWebPageInit':
            include_once MODX_BASE_PATH . 'assets/plugins/pirschAnalytics/pirschAnalytics.php';
            break;
    }
}