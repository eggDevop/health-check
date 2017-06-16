<?php
namespace EggDigital\HealthCheck\Classes;

class Cassandra extends Base
{
    public function connect($node, $try = 0)
    {

        if (isset($node['keyspace'])) {
            $connection = new Cassandra\Connection($node, $node['keyspace']);
        } else {
            $connection = new Cassandra\Connection($node);
        }

        try {
            $connection->connect();
        } catch (\Exception $e) {
            if ($try < 3) {
                $try++;
                return $this->checkConnectCassandra($node, $try);
            }
            $connection = null;
        }

        // Set consistency level for farther requests (default is CONSISTENCY_ONE)
        //$connection->setConsistency(Request::CONSISTENCY_QUORUM);
        return $connection;
    }

    public function getData($cassandra, $cql = null)
    {

        if (empty($cql)) {
            $cql = "SELECT count(*) FROM noti_request WHERE app_id = 14 ALLOW FILTERING";
        }

        $statement = $cassandra->queryAsync($cql);

        // Wait until received the response, can be reversed order
        $result = $statement->getResponse();
        $result = $result->fetchRow()['count'];

        return $result;
    }
}
