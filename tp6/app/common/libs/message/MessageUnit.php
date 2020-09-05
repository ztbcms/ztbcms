<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-09-04
 * Time: 20:12.
 */

namespace app\common\libs\message;


use app\common\model\message\MessageModel;

abstract class MessageUnit
{
    //标题
    protected $title = '';
    //消息内容
    protected $content = '';
    protected $target = '';
    protected $target_type = '';
    protected $sender = '';
    protected $sender_type = '';
    protected $receiver = '';
    protected $receiver_type = '';
    //消息类型，默认notice通知消息
    protected $type = 'notice';

    /**
     * @return bool
     */
    abstract static function getSenders();

    function createMessage()
    {
        $message = new MessageModel();
        $message->title = $this->getTitle();
        $message->content = $this->getContent();
        $message->target = $this->getTarget();
        $message->target_type = $this->getTargetType();
        $message->sender = $this->getSender();
        $message->sender_type = $this->getSenderType();
        $message->receiver = $this->getReceiver();
        $message->receiver_type = $this->getReceiverType();
        $message->type = $this->getType();
        $message->create_time = time();
        $message->class = get_class($this);
        $message->save();
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTargetType(): string
    {
        return $this->target_type;
    }

    /**
     * @param string $target_type
     */
    public function setTargetType(string $target_type): void
    {
        $this->target_type = $target_type;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @param string $sender
     */
    public function setSender(string $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return string
     */
    public function getSenderType(): string
    {
        return $this->sender_type;
    }

    /**
     * @param string $sender_type
     */
    public function setSenderType(string $sender_type): void
    {
        $this->sender_type = $sender_type;
    }

    /**
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->receiver;
    }

    /**
     * @param string $receiver
     */
    public function setReceiver(string $receiver): void
    {
        $this->receiver = $receiver;
    }

    /**
     * @return string
     */
    public function getReceiverType(): string
    {
        return $this->receiver_type;
    }

    /**
     * @param string $receiver_type
     */
    public function setReceiverType(string $receiver_type): void
    {
        $this->receiver_type = $receiver_type;
    }

}