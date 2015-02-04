<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
 
namespace ersEntity\Entity;

use BjyAuthorize\Provider\Role\ProviderInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * An example of how to implement a role aware user entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks()
 *
 * @author Tom Oram <tom@scl.co.uk>
 */
class User implements UserInterface, ProviderInterface
{
    /**
     * Instance of InputFilterInterface.
     *
     * @var InputFilter
     */
    private $inputFilter;
    
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true,  length=255)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $prename;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $surname;
    
    /**
     * @var string
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    protected $password;

    /**
     * @var int
     */
    protected $state;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $birthday;
    
    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="user_role_linker",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;
    
    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="user")
     * @ORM\JoinColumn(name="id", referencedColumnName="User_id")
     */
    protected $logs;

    /**
     * @ORM\OneToMany(targetEntity="Match", mappedBy="user")
     * @ORM\JoinColumn(name="id", referencedColumnName="Admin_id")
     */
    protected $matches;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="purchaser")
     * @ORM\JoinColumn(name="id", referencedColumnName="Purchaser_id")
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="Package", mappedBy="participant")
     * @ORM\JoinColumn(name="id", referencedColumnName="Participant_id")
     */
    protected $packages;

    /**
     * Initialies the roles variable.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->matches = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->packages = new ArrayCollection();
    }
    
    /**
     * @ORM\PrePersist
     */
    public function PrePersist()
    {
        if(!isset($this->created)) {
            $this->created = new \DateTime();
        }
        $this->updated = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function getUserId()
    {
        return $this->getId();
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }
    public function setUserId($id)
    {
        $this->setId($id);
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName
     *
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * Set the value of prename.
     *
     * @param string $prename
     * @return \Entity\User
     */
    public function setPrename($prename)
    {
        $this->prename = $prename;

        return $this;
    }

    /**
     * Get the value of prename.
     *
     * @return string
     */
    public function getPrename()
    {
        return $this->prename;
    }

    /**
     * Set the value of surname.
     *
     * @param string $surname
     * @return \Entity\User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get the value of surname.
     *
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
    
    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * Set the value of active.
     *
     * @param boolean $active
     * @return \Entity\User
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of active.
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    
    /**
     * Set the value of birthday.
     *
     * @param datetime $birthday
     * @return \Entity\User
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get the value of birthday.
     *
     * @return datetime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }
    
    /**
     * Set the value of updated.
     *
     * @param datetime $updated
     * @return \Entity\ProductVariant
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get the value of updated.
     *
     * @return datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set the value of created.
     *
     * @param datetime $created
     * @return \Entity\ProductVariant
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get the value of created.
     *
     * @return datetime
     */
    public function getCreated()
    {
        return $this->created;
    }
    
    /**
     * Get role.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->getValues();
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role
     *
     * @return void
     */
    public function addRole($role)
    {
        $this->roles[] = $role;
    }
    
    /**
     * Add Log entity to collection (one to many).
     *
     * @param \Entity\Log $log
     * @return \Entity\User
     */
    public function addLog(Log $log)
    {
        $this->logs[] = $log;

        return $this;
    }

    /**
     * Remove Log entity from collection (one to many).
     *
     * @param \Entity\Log $log
     * @return \Entity\User
     */
    public function removeLog(Log $log)
    {
        $this->logs->removeElement($log);

        return $this;
    }

    /**
     * Get Log entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add Match entity to collection (one to many).
     *
     * @param \Entity\Match $match
     * @return \Entity\User
     */
    public function addMatch(Match $match)
    {
        $this->matches[] = $match;

        return $this;
    }

    /**
     * Remove Match entity from collection (one to many).
     *
     * @param \Entity\Match $match
     * @return \Entity\User
     */
    public function removeMatch(Match $match)
    {
        $this->matches->removeElement($match);

        return $this;
    }

    /**
     * Get Match entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * Add Order entity to collection (one to many).
     *
     * @param \Entity\Order $order
     * @return \Entity\User
     */
    public function addOrder(Order $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove Order entity from collection (one to many).
     *
     * @param \Entity\Order $order
     * @return \Entity\User
     */
    public function removeOrder(Order $order)
    {
        $this->orders->removeElement($order);

        return $this;
    }

    /**
     * Get Order entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add Package entity to collection (one to many).
     *
     * @param \Entity\Package $package
     * @return \Entity\User
     */
    public function addPackage(Package $package)
    {
        $this->packages[] = $package;

        return $this;
    }

    /**
     * Remove Package entity from collection (one to many).
     *
     * @param \Entity\Package $package
     * @return \Entity\User
     */
    public function removePackage(Package $package)
    {
        $this->packages->removeElement($package);

        return $this;
    }

    /**
     * Get Package entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPackages()
    {
        return $this->packages;
    }
    
    /**
     * Not used, Only defined to be compatible with InputFilterAwareInterface.
     * 
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used.");
    }

    /**
     * Return a for this entity configured input filter instance.
     *
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter instanceof InputFilterInterface) {
            return $this->inputFilter;
        }
        $factory = new InputFactory();
        $filters = array(
            array(
                'name' => 'user_id',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'username',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'email',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'display_name',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'password',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'state',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'prename',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'surname',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'active',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'birthday',
                'required' => true,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'updated',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
            array(
                'name' => 'created',
                'required' => false,
                'filters' => array(),
                'validators' => array(),
            ),
        );
        $this->inputFilter = $factory->createInputFilter($filters);

        return $this->inputFilter;
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
        $dataFields = array('id', 'username', 'email', 'display_name', 'password', 'state', 'prename', 'surname', 'active', 'birthday', 'updated', 'created');
        $relationFields = array();
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
        return array('user_id', 'username', 'email', 'display_name', 'password', 'state', 'prename', 'surname', 'emailCopy', 'active', 'birthday', 'updated', 'created');
    }
}
