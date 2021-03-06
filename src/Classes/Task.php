<?php
declare(strict_types=1);
namespace TaskForce;
use \Exceptions\CustomException;
class Task
{
    const STATUS_NEW = 'new'; // Новое
    const STATUS_CANCELED = 'canceled'; // Отменено
    const STATUS_INWORK = 'inwork'; // В работе
    const STATUS_COMPLETE = 'complete'; // Выполнено
    const STATUS_FAILED = 'failed'; // Провалено

    const ACTION_PUBLISH = 'publish';
    const ACTION_CANCEL = 'cancel';
    const ACTION_CHOOSE = 'choose';
    const ACTION_MARK_DONE = 'mark_done';
    const ACTION_REFUSE = 'failed';
    const ACTION_RESPOND = 'respond';
    const ACTION_WRITE_MESSAGE = 'write_message';

    private $executantID = 0;
    private $clientID = 0;
    private $currentUserID = 0;
    private $status = [];
    private $actions = [];

    public $actionsList = [
        'publish' => 'Опубликовать задание',
        'cancel' => 'Отменить задание',
        'choose' => 'Выбрать исполнителя',
        'mark_done' => 'Отметить задание как выполненное',
        'refuse' => 'Отказаться от задания',
        'respond' => 'Откликнуться на задание',
    ];

    public $statusesList = [
        'new' => 'Задание опубликовано, исполнитель ещё не найден',
        'canceled' => 'Заказчик отменил задание',
        'inwork' => 'Заказчик выбрал исполнителя для задания',
        'complete' => 'Заказчик отметил задание как выполненное',
        'failed' => 'Исполнитель отказался от выполнения задания',
    ];

    private $actionStatusList = [
    	self::ACTION_PUBLISH => self::STATUS_NEW,
    	self::ACTION_CANCEL => self::STATUS_CANCELED,
    	self::ACTION_CHOOSE => self::STATUS_INWORK,
    	self::ACTION_MARK_DONE => self::STATUS_COMPLETE,
    	self::ACTION_REFUSE => self::STATUS_FAILED,
    ];

    private $actionStatusListByRole = [
    	'executant' => [
    		self::STATUS_NEW => [self::ACTION_RESPOND, self::ACTION_WRITE_MESSAGE],
    		self::STATUS_INWORK => [self::ACTION_REFUSE],
    	],
    	'client' => [
    		self::STATUS_NEW => [self::ACTION_PUBLISH, self::ACTION_CHOOSE],
    		self::STATUS_INWORK => [self::ACTION_MARK_DONE],
    	],
    ];

    function __construct($executantID, $clientID, $currentUserID)
    {
        if (!is_int($executantID) || !is_int($clientID) || !is_int($currentUserID)) {
            throw new CustomException("ID must be numeric");
        }

        $this->executantID = $executantID;
        $this->clientID = $clientID;
        $this->currentUserID = $currentUserID;
    }

    public function getAvailableActionsByStatus($status, $role) : array
    {
        return $this->actionStatusListByRole[$role][$status];
    }

    public function getStatus(AbstractAction $action) : object
    {
        if (is_subclass_of($action, 'AbstractAction')) {
            throw new CustomException("Action must be instance of AbstractAction");
        }

        if (!$action->checkRole($this->executantID, $this->clientID, $this->currentUserID)) {
            throw new CustomException("403 Forbidden");
        }

        return $action;
    }

    public function getStatusList() : array
    {
    	return $this->statusesList;
    }

    public function getActionsList() : array
    {
    	return $this->actionsList;
    }
}
