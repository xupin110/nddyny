<?php

namespace app\Models\nddyny\WebDriver;

use R;
use app\Controllers\nddyny\Common\ModelWebdriver;
use app\Controllers\nddyny\Common\Process;

class WowWebDriver extends ModelWebdriver
{

    private $url = 'http://armory.rpwow.com/item-tooltip.xml?i=';

    private $error_name = 'Error!';

    public function index(Process $Process)
    {
        $this->init($Process);
        $params = json_decode($this->process->data['params']);
        $start = $params->start;
        $end = $params->end;
        $i = $start;
        $startEmpty = false;
        $this->process->renderGroup(R::none("开始，从{$start}到{$end}"));
        do {
            usleep(1000000 * 0.3);
            $url = $this->url . $i;
            $cnt = 0;
            $str = '';
            try {
                // 防超时
                while($cnt < 3 && ($str = @file_get_contents($url)) === false)
                    $cnt++;
            } catch (\Exception $e) {
                // 防502
                $this->process->renderGroup(R::none($e->getMessage()));
                $i--;
                usleep(1000000 * 15);
                continue;
            }
            try {
                $obj = json_decode(json_encode(simplexml_load_string($str)));
            } catch (\Exception $e) {
                $this->save(['id' => $i]);
                $this->process->renderGroup(R::none("{$i} <span style='color: #f00'>{$this->error_name}</span>"));
                continue;
            }
            if (empty($item = $obj->itemTooltips->itemTooltip) || !isset($item->id)) {
                if(!$startEmpty) {
                    $startEmpty = true;
                    $this->process->renderGroup(R::none("空 开始{$i}, "), false);
                } else {
                    $this->process->renderGroup(R::none("{$i}, "), false);
                }
                continue;
            }
            if($startEmpty) {
                $startEmpty = false;
                $this->process->renderGroup(R::none("空 结束"));
            }
            $item = json_decode(json_encode($item), true);
            $content = "{$i} <span style='color: #f00'>{$item['name']}</span>";
            if(R::noSuccess($this->save($item))) {
                $content .= " 重复";
            }
            $this->process->renderGroup(R::none($content));
        } while ($i++ < $end);
        return R::success();
    }

    private function save($item)
    {
        $sql = $this->mysql_pool->dbQueryBuilder->from('nddyny_rpwow_item')->select('item_id')->where('item_id', $item['id']);
        $ret = $this->loader->model('nddyny\Table\Table', $this)->findField($sql);
        if ($ret) {
            return R::none('已存在');
        }
        $db = $this->mysql_pool->dbQueryBuilder->insert('nddyny_rpwow_item');
        $db->set('item_id', $item['id']);
        if(count($item) == 1) {
            $db->set('name', $this->error_name);
            $db->query();
            return R::success();
        }
        unset($item['id']);
        foreach ([
                     'name',
                     'overallQualityId',
                     'bonding',
                     'maxCount',
                     'classId',
                     'requiredLevel',
                 ] as $key) {
            $db->set($key, $item[$key]);
            unset($item[$key]);
        }
        if(isset($item['desc'])) {
            $db->set('item_desc', $item['desc']);
            unset($item['desc']);
        }
        if(isset($item['equipData'])) {
            if(isset($item['equipData']['inventoryType'])) {
                $db->set('inventoryType', $item['equipData']['inventoryType']);
                unset($item['equipData']['inventoryType']);
            }
            if(isset($item['equipData']['subclassName'])) {
                if(!is_array($item['equipData']['subclassName'])) {
                    $item['equipData']['subclassName'] = [$item['equipData']['subclassName']];
                }
                foreach ($item['equipData']['subclassName'] as $key => $value) {
                    $db->set('subclassName', $value);
                }
                unset($item['equipData']['subclassName']);
            }
            if (isset($item['equipData']['containerSlots'])) {
                if(!is_array($item['equipData']['containerSlots'])) {
                    $item['equipData']['containerSlots'] = [$item['equipData']['containerSlots']];
                }
                foreach ($item['equipData']['containerSlots'] as $key => $value) {
                    $db->set('containerSlots', $value);
                }
                unset($item['equipData']['containerSlots']);
            }
            if (count($item['equipData']) === 0) {
                unset($item['equipData']);
            }
        }
        if(isset($item['allowableClasses'])) {
            if (!is_array($item['allowableClasses']['class'])) {
                $item['allowableClasses']['class'] = [$item['allowableClasses']['class']];
            }
            $i = 1;
            foreach ($item['allowableClasses']['class'] as $key => $value) {
                $db->set("allowableClasse$i", $value);
                $i++;
            }
            unset($item['allowableClasses']['class']);
            if (count($item['allowableClasses']) === 0) {
                unset($item['allowableClasses']);
            }
        }
        if(isset($item['spellData'])) {
            if(isset($item['spellData']['spell'])) {
                if (isset($item['spellData']['spell']['desc'])) {
                    $item['spellData']['spell'] = [$item['spellData']['spell']];
                }
                $i = 1;
                foreach ($item['spellData']['spell'] as $key => $info) {
                    $db->set("spell$i", $info['desc']);
                    $i++;
                }
                unset($item['spellData']['spell']);
            }
            if (count($item['spellData']) === 0) {
                unset($item['spellData']);
            }
        }
        if(isset($item['setData'])) {
            if(isset($item['setData']['setBonus'])) {
                $i = 1;
                foreach ($item['setData']['setBonus'] as $key => $info) {
                    if (isset($info['@attributes'])) {
                        $info['desc'] = $info['@attributes']['desc'];
                    }
                    $db->set("setBonusDesc$i", $info['desc']);
                    $i++;
                }
                unset($item['setData']['setBonus']);
            }
            unset($item['setData']['name']);
            unset($item['setData']['item']);
            if (count($item['setData']) === 0) {
                unset($item['setData']);
            }
        }
        $key = '@attributes';
        if(isset($item['itemSource'])) {
            if(isset($item['itemSource'][$key])) {
                if(isset($item['itemSource'][$key]['value'])) {
                    $db->set('source', $item['itemSource'][$key]['value']);
                    unset($item['itemSource'][$key]['value']);
                }
            }
            if (count($item['itemSource'][$key]) === 0) {
                unset($item['itemSource'][$key]);
            }
            if (count($item['itemSource']) === 0) {
                unset($item['itemSource']);
            }
        }
        foreach ([
                     'itemLevel',
                     'armor',
                     'bonusStrength',
                     'bonusAgility',
                     'bonusIntellect',
                     'bonusSpirit',
                     'bonusStamina',
                     'bonusHitRating',
                     'bonusHasteRating',
                     'bonusCritRating',
                     'bonusResilienceRating',
                     'bonusSpellPower',
                     'bonusAttackPower',
                     'bonusDefenseSkillRating',
                     'bonusDodgeRating',
                     'bonusParryRating',
                     'bonusExpertiseRating',
                     'bonusArmorPenetration',
                     'gemProperties',
                     'natureResist',
                     'bonusBlockRating',
                     'shadowResist',
                     'arcaneResist',
                     'fireResist',
                     'natureResist',
                     'frostResist',
                     'bonusSpellPenetration',
                     'blockValue'
                 ] as $key) {
            if (isset($item[$key]) && (!empty($item[$key]) || $item[$key] === '0')) {
                if (is_array($item[$key])) {
                    $data = 0;
                    foreach ($item[$key] as $value) {
                        $data += $value;
                    }
                    $item[$key] = $data;
                }
                $db->set($key, $item[$key]);
                unset($item[$key]);
            }
        }
        $db->query();
        unset($item['icon']);
        unset($item['damageData']);
        unset($item['socketData']);
        unset($item['durability']);
        unset($item['randomEnchantData']);
        unset($item['requiredSkill']);
        unset($item['bonusManaRegen']);
        unset($item['bonusHealthRegen']);
        unset($item['requiredFaction']);
        unset($item['heroic']);
        unset($item['allowableRaces']);
        if(count($item) > 0) {
            $this->process->renderGroup(R::none('未处理的字段: ' . json_encode($item)));
        }
        return R::success();
    }

    protected function getCookieRedisKey()
    {
        return 'test.test.test';
    }
}