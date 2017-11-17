<?php

/**
 * Auto generated by MySQL Workbench Schema Exporter.
 * Version 2.1.6-dev (doctrine2-mappedsuperclass) on 2017-07-19 23:21:08.
 * Goto https://github.com/johmue/mysql-workbench-schema-exporter for more
 * information.
 */

namespace ErsBase\Entity\Base;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErsBase\Entity\Base\MailqHasUser
 *
 * @ORM\MappedSuperclass
 * @ORM\Table(name="`mailq_has_user`", indexes={@ORM\Index(name="fk_mailq_has_user_user4_idx", columns={"`user_id`"}), @ORM\Index(name="fk_mailq_has_user_mailq4_idx", columns={"`mailq_id`"})})
 */
abstract class MailqHasUser
{
    /**
     * @ORM\Id
     * @ORM\Column(name="`mailq_id`", type="integer")
     */
    protected $mailq_id;

    /**
     * @ORM\Id
     * @ORM\Column(name="`user_id`", type="integer")
     */
    protected $user_id;

    /**
     * @ORM\Column(name="`type`", type="string", length=45, nullable=true)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Mailq", inversedBy="mailqHasUsers", cascade={"remove"})
     * @ORM\JoinColumn(name="`mailq_id`", referencedColumnName="`id`")
     */
    protected $mailq;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="mailqHasUsers")
     * @ORM\JoinColumn(name="`user_id`", referencedColumnName="`id`")
     */
    protected $user;

    public function __construct()
    {
    }

    /**
     * Set the value of mailq_id.
     *
     * @param integer $mailq_id
     * @return \ErsBase\Entity\Base\MailqHasUser
     */
    public function setMailqId($mailq_id)
    {
        $this->mailq_id = $mailq_id;

        return $this;
    }

    /**
     * Get the value of mailq_id.
     *
     * @return integer
     */
    public function getMailqId()
    {
        return $this->mailq_id;
    }

    /**
     * Set the value of user_id.
     *
     * @param integer $user_id
     * @return \ErsBase\Entity\Base\MailqHasUser
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get the value of user_id.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set the value of type.
     *
     * @param string $type
     * @return \ErsBase\Entity\Base\MailqHasUser
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Mailq entity (many to one).
     *
     * @param \ErsBase\Entity\Base\Mailq $mailq
     * @return \ErsBase\Entity\Base\MailqHasUser
     */
    public function setMailq(Mailq $mailq = null)
    {
        $this->mailq = $mailq;

        return $this;
    }

    /**
     * Get Mailq entity (many to one).
     *
     * @return \ErsBase\Entity\Base\Mailq
     */
    public function getMailq()
    {
        return $this->mailq;
    }

    /**
     * Set User entity (many to one).
     *
     * @param \ErsBase\Entity\Base\User $user
     * @return \ErsBase\Entity\Base\MailqHasUser
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User entity (many to one).
     *
     * @return \ErsBase\Entity\Base\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Populate entity with the given data.
     * The set* method will be used to set the data.
     *
     * @param array $data
     * @return boolean
     */
    public function populate(array $data = array())
    {
        foreach ($data as $field => $value) {
            $setter = sprintf('set%s', ucfirst(
                str_replace(' ', '', ucwords(str_replace('_', ' ', $field)))
            ));
            if (method_exists($this, $setter)) {
                $this->{$setter}($value);
            }
        }

        return true;
    }

    /**
     * Return a array with all fields and data.
     * Default the relations will be ignored.
     * 
     * @param array $fields
     * @return array
     */
    public function getArrayCopy(array $fields = array())
    {
        $dataFields = array('mailq_id', 'user_id', 'type');
        $relationFields = array('mailq', 'user');
        $copiedFields = array();
        foreach ($relationFields as $relationField) {
            $map = null;
            if (array_key_exists($relationField, $fields)) {
                $map = $fields[$relationField];
                $fields[] = $relationField;
                unset($fields[$relationField]);
            }
            if (!in_array($relationField, $fields)) {
                continue;
            }
            $getter = sprintf('get%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $relationField)))));
            $relationEntity = $this->{$getter}();
            $copiedFields[$relationField] = (!is_null($map))
                ? $relationEntity->getArrayCopy($map)
                : $relationEntity->getArrayCopy();
            $fields = array_diff($fields, array($relationField));
        }
        foreach ($dataFields as $dataField) {
            if (!in_array($dataField, $fields) && !empty($fields)) {
                continue;
            }
            $getter = sprintf('get%s', ucfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $dataField)))));
            $copiedFields[$dataField] = $this->{$getter}();
        }

        return $copiedFields;
    }

    public function __sleep()
    {
        return array('mailq_id', 'user_id', 'type');
    }
}