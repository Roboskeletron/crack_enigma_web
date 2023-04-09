<?php

class Cyphertext implements IModel
{
    private $id;
    private $name;
    private $author;
    private $text;
    private $encrypted;
    private $code;
    private $total_attempts;
    private $successful_attempts;

    /**
     * @param $id
     * @param $name
     * @param $author
     * @param $text
     * @param $encrypted
     * @param $code
     * @param $total_attempts
     * @param $successful_attempts
     */
    public function __construct($id, $name, $author, $text, $encrypted, $code, $total_attempts, $successful_attempts)
    {
        $this->id = $id;
        $this->setName($name);
        $this->setAuthor($author);
        $this->setText($text);
        $this->setEncrypted($encrypted);
        $this->setCode($code);
        $this->setTotalAttempts($total_attempts);
        $this->setSuccessfulAttempts($successful_attempts);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getEncrypted(): string
    {
        return $this->encrypted;
    }

    /**
     * @param string $encrypted
     */
    public function setEncrypted(string $encrypted): void
    {
        $this->encrypted = $encrypted;
    }

    /**
     * @return array
     */
    public function getCode(): array
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = json_decode($code, true);
    }

    /**
     * @return int
     */
    public function getTotalAttempts(): int
    {
        return $this->total_attempts;
    }

    /**
     * @param int $total_attempts
     */
    public function setTotalAttempts(int $total_attempts): void
    {
        $this->total_attempts = $total_attempts;
    }

    /**
     * @return int
     */
    public function getSuccessfulAttempts(): int
    {
        return $this->successful_attempts;
    }

    /**
     * @param int $successful_attempts
     */
    public function setSuccessfulAttempts(int $successful_attempts): void
    {
        $this->successful_attempts = $successful_attempts;
    }

    public function increaseAttempts(bool $successful = false)
    {
        if ($successful)
            $this->successful_attempts++;
        $this->total_attempts++;
    }

    public static function fetch($data): Cyphertext
    {
        $id = $data['id'];
        $name = $data['name'];
        $author = $data['author'];
        $text = $data['text'];
        $encrypted = $data['encrypted'];
        $code = $data['code'];
        $total_attempts = $data['total attempts'];
        $successful_attempts = $data['successful attempts'];

        return new Cyphertext($id, $name, $author, $text, $encrypted, $code, $total_attempts, $successful_attempts);
    }

    public static function fetch_by_id($database, $id): ?Cyphertext
    {
        $result = $database->sql_query('select * from cyphertexts where id = $1', array($id));

        $result = $database->get_array($result);

        if (count($result) == 0) {
            response_with_message(404, "cyphertext not found");
            return null;
        }

        return Cyphertext::fetch($result[0]);
    }
}