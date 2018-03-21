<?php
declare(strict_types=1);

namespace GeorgRinger\Gdpr\Xclass;

use GeorgRinger\Gdpr\Service\IpAnonymizer;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SearchController extends \TYPO3\CMS\IndexedSearch\Controller\SearchController
{

    /**
     * Write statistics information to database for the search operation if there was at least one search word.
     *
     * @param array $searchParams search params
     * @param array $searchWords Search Word array
     * @param int $count Number of hits
     * @param array $pt Milliseconds the search took (start time DB query + end time DB query + end time to compile results)
     */
    protected function writeSearchStat($searchParams, $searchWords, $count, $pt)
    {
        $searchWord = $this->getSword();
        if (empty($searchWord) && empty($searchWords)) {
            return;
        }

        $insertFields = [
            'searchstring' => $searchWord,
            'searchoptions' => serialize([$searchParams, $searchWords, $pt]),
            'feuser_id' => (int)$GLOBALS['TSFE']->fe_user->user['uid'],
            // cookie as set or retrieved. If people has cookies disabled this will vary all the time
            'cookie' => $GLOBALS['TSFE']->fe_user->id,
            // Remote IP address
            'IP' => IpAnonymizer::anonymizeIp(GeneralUtility::getIndpEnv('REMOTE_ADDR')),
            // Number of hits on the search
            'hits' => (int)$count,
            // Time stamp
            'tstamp' => $GLOBALS['EXEC_TIME']
        ];
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('index_search_stat');
        $connection->insert(
            'index_stat_search',
            $insertFields,
            ['searchoptions' => Connection::PARAM_LOB]
        );
        $newId = $connection->lastInsertId('index_stat_search');
        if ($newId) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('index_stat_word');
            foreach ($searchWords as $val) {
                $insertFields = [
                    'word' => $val['sword'],
                    'index_stat_search_id' => $newId,
                    // Time stamp
                    'tstamp' => $GLOBALS['EXEC_TIME'],
                    // search page id for indexed search stats
                    'pageid' => $GLOBALS['TSFE']->id
                ];
                $connection->insert('index_stat_word', $insertFields);
            }
        }
    }
}