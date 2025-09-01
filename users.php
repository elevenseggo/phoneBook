<?php

class ActiveDirectory {
    private $connection;
    private $baseDn;

    public function __construct($host, $user, $password, $baseDn) {
        $this->connection = ldap_connect($host);
        if (!$this->connection) {
            throw new Exception("Ошибка: не удалось подключиться к серверу Active Directory.");
        }

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($this->connection, "$user@kubanfarm", $password)) {
            throw new Exception("Ошибка: неверные учетные данные или недостаточно прав.");
        }

        $this->baseDn = $baseDn;
    }
    
    public function getUsers($filter) {
        $attributes = ["cn", "title", "mail", "ipphone", "telephonenumber", "mobile", "distinguishedname"];
        $search = ldap_search($this->connection, $this->baseDn, $filter, $attributes);
        if (!$search) {
            return [];
        }

        $entries = ldap_get_entries($this->connection, $search);
       
        return $this->formatResults($entries);
    }

    private function formatResults($entries) {
        $users = [];
        for ($i = 1; $i < $entries["count"]; $i++) {
            $users[] = [
                "cn" => $entries[$i]["cn"][0] ?? "Нет данных",
                "title" => $entries[$i]["title"][0] ?? "Нет данных",
                "mail" => $entries[$i]["mail"][0] ?? "Нет данных",
                "ipphone" => $entries[$i]["ipphone"][0] ?? "Нет данных",
                "telephonenumber" => $entries[$i]["telephonenumber"][0] ?? "Нет данных",
                "mobile" => $entries[$i]["mobile"][0] ?? "Нет данных",
                "distinguishedname" => $entries[$i]["distinguishedname"][0] ?? "Нет данных"
            ];
        }
        return $users;
    }

    public function __destruct() {
        ldap_close($this->connection);
    }

}