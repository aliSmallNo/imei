<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\BaseObject;

/**
 * CompositeUrlRule is the base class for URL rule classes that consist of multiple simpler rules.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class CompositeUrlRule extends BaseObject implements UrlRuleInterface
{
    /**
     * @var UrlRuleInterface[] the URL rules contained in this composite rule.
     * This property is set in [[init()]] by the return value of [[createRules()]].
     */
    protected $rules = [];


    /**
     * Creates the URL rules that should be contained within this composite rule.
     * @return UrlRuleInterface[] the URL rules
     */
    abstract protected function createRules();

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->rules = $this->createRules();
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            $result = $rule->parseRequest($manager, $request);
            if (YII_DEBUG) {
                Yii::trace([
                    'rule' => method_exists($rule, '__toString') ? $rule->__toString() : get_class($rule),
                    'match' => $result !== false,
                    'parent' => self::className()
                ], __METHOD__);
            }
            if ($result !== false) {
                return $result;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        foreach ($this->rules as $rule) {
            /* @var $rule \yii\web\UrlRule */
            if (($url = $rule->createUrl($manager, $route, $params)) !== false) {
                return $url;
            }
        }

        return false;
    }
}
