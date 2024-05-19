<?php
include_once "./net_funcs.php";
include_once "./settings.php";
include_once "./HTTP_STATES.php";

class Connection {
    private mysqli $connection;
    private mysqli_stmt $statement;
    private mysqli_result|false $result;
    private bool $statement_open = false;

    public function __construct(string $hostname, string $username, string $password, string $database, int $port, ?string $socket = null) {
        try {
            $this->connection = new mysqli($hostname, $username, $password, $database, $port, $socket);
        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function executeWithResponse(string $query, ?array $args = null): array|false {
        try {
            $this->execute($query, $args);
            $return_data = [];

            while ($hash = $this->result->fetch_assoc()) {
                array_push($return_data, $hash);
            }

            return $return_data;

        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
        return false;
    }

    public function execute(string $query, ?array $args = null): void {
        try {
            $this->prepareStatement($query);
            $this->getExecResult($args);

        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function prepareStatement(string $query): void {
        $this->statement_open = true;
        $this->statement = $this->connection->prepare($query);
    }

    public function getExecResult(?array $args): void {
        if (!$this->statement->execute($args)) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, "Failed to execute statement!");
        } else {
            $this->result = $this->statement->get_result();
        }
    }

    public function closeConnection(): void {
        try {
            $this->connection->close();
        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function closeStatement(): void {
        try {
            $this->statement->close();
            $this->statement_open = false;
        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function closeResult(): void {
        try {
            if ($this->result !== false) {
                $this->result->close();
            }
        } catch (Exception $exception) {
            status_exit(HTTP_STATES::INTERNAL_SERVER_ERROR, $exception->getMessage());
        }
    }

    public function getStatement(): mysqli_stmt {
        return $this->statement;
    }

    public function getResult(): mysqli_result {
        return $this->result;
    }

    public function getConnection(): mysqli {
        return $this->connection;
    }

}
