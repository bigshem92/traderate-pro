<?php
// app/Database/Connection.php
namespace App\Database;

class Connection {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $this->connection = new \mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_DATABASE']
        );

        if ($this->connection->connect_error) {
            throw new \Exception("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result();
    }
}

// app/Database/QueryBuilder.php
namespace App\Database;

class QueryBuilder {
    protected $table;
    protected $where = [];
    protected $orderBy = [];
    protected $limit;
    protected $offset;

    public function __construct($table) {
        $this->table = $table;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->where[] = [$column, $operator, $value];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = [$column, $direction];
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function get() {
        $sql = $this->buildQuery();
        $result = Connection::getInstance()->query($sql, $this->getBindings());
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function buildQuery() {
        $sql = "SELECT * FROM {$this->table}";

        if ($this->where) {
            $sql .= " WHERE " . $this->buildWhere();
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY " . $this->buildOrderBy();
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    private function buildWhere() {
        return implode(' AND ', array_map(function($where) {
            return "{$where[0]} {$where[1]} ?";
        }, $this->where));
    }

    private function getBindings() {
        return array_map(function($where) {
            return $where[2];
        }, $this->where);
    }
}
