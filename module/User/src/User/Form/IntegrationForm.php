<?php
/**
 * DBShop 电子商务系统
 *
 * ==========================================================================
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2015 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 * ==========================================================================
 *
 * @author    静静的风
 *
 */

namespace User\Form;

class IntegrationForm
{
    public function setFormValue (array $integrationConfig, array $data)
    {
        foreach ($integrationConfig as $key => $value) {
            $integrationConfig[$key] = is_array($value) ? $this->getSetFormType($value, $data) : $value;
        }
        //接口状态
        if(isset($data['integration_state']) and !empty($data['integration_state'])) $integrationConfig['integration_state'] = $data['integration_state'];

        return $integrationConfig;
    }
    private function getSetFormType (array $value, array $data)
    {
        switch ($value['input_type']) {
            case 'hidden':
            case 'text':
            case 'textarea':
            case 'image':
                $value['content'] = $data[$value['name_id']];
                break;
            case 'select':
                $value['selected'] = $data[$value['name_id']];
                break;
            case 'radio':
                $value['checked'] = $data[$value['name_id']];
                break;
            case 'checkbox':
                $value['checked'] = $data[$value['name_id']];
                break;
        }
        return $value;
    }
    /**
     * 获取表单数组
     * @return multitype:multitype:string array  Ambigous <\Payment\Service\multitype:string, multitype:string array >
     */
    public function createFormInput(array $integrationConfig)
    {
        $inputArray = array();
        foreach($integrationConfig as $value) {
            switch ($value['input_type']) {
                case 'hidden':
                    $inputArray[] = $this->getHiddenInput($value);
                    break;
                case 'text':
                    $inputArray[] = $this->getTextInput($value);
                    break;
                case 'select':
                    $inputArray[] = $this->getSelectInput($value);
                    break;
                case 'radio':
                    $inputArray[] = $this->getRadioInput($value);
                    break;
                case 'textarea':
                    $inputArray[] = $this->getTextareaInput($value);
                    break;
                case 'image':
                    $inputArray[] = $this->getImageInput($value);
                    break;
                case 'checkbox':
                    $inputArray[] = $this->getCheckBox($value);
                    break;
            }
        }
        //对订单状态进行处理
        if(isset($integrationConfig['integration_state']) and !empty($integrationConfig['integration_state'])) {
             $inputArray['integration_state'] = $integrationConfig['integration_state'];
        }
            
        return $inputArray;
    }
    /**
     * 获取隐藏表单
     * @param array $data
     */
    private function getHiddenInput (array $data)
    {
        $array = array();
        $array['name']  = $data['title'];
        $array['input'] = $data['content'];
        $array['type']  = $data['input_type'];
        $array['hidden']= '<input type="hidden" name="' . $data['name_id'] . '" id="' . $data['name_id'] . '" value="' . $data['content'] . '" />';
    
        return $array;
    }
    /**
     * 获取输入框
     * @param array $data
     */
    private function getTextInput (array $data)
    {
        $array = array();
        $array['name'] = $data['title'];
        $array['input']= '<input type="'.(strpos($data['name_id'], 'passwd') !== false ? 'password' : 'text').'" class="' . $data['class'] . '" name="' . $data['name_id'] . '" '.($data['name_id']=='integration_name' ? 'readonly="readonly"' : '').' id="' . $data['name_id'] . '" value="' . $data['content'] . '" />';
        $array['type'] = $data['input_type'];
    
        return $array;
    }
    /**
     * 获取下拉
     * @param array $data
     */
    private function getSelectInput (array $data)
    {
        $array = array();
        $array['name'] = $data['title'];
        $array['input']= '<select class="span2" name="' . $data['name_id'] . '" id="' . $data['name_id'] . '">';
        foreach ($data['content'] as $value) {
            $array['input'] .= '<option value="' . $value['value'] . '" ' . ($data['selected'] == $value['value'] ? 'selected="selected"' : '') . '>' . $value['name'] . '</option>';
        }
        $array['input'] .= '</select>';
        $array['type'] = $data['input_type'];
    
        return $array;
    }
    /**
     * 获取单选
     * @param array $data
     */
    private function getRadioInput (array $data)
    {
        $array = array();
        $array['name']  = $data['title'];
        $array['input'] = '';
        foreach ($data['content'] as $value) {
            $array['input'] .= '<label class="radio inline"><input type="radio" name="' . $data['name_id'] . '" ' . ($data['checked'] == $value['value'] ? 'checked="checked"' : '') . ' value="' . $value['value'] . '" />' . $value['name'] . '</label>';
        }
        $array['type'] = $data['input_type'];
    
        return $array;
    }
    /**
     * 获取文本域
     * @param array $data
     */
    private function getTextareaInput (array $data)
    {
        $array = array();
        $array['name'] = $data['title'];
        $array['input']= '<textarea class="' . $data['class'] . '" name="' . $data['name_id'] . '" id="' . $data['name_id'] . '" rows="3">' . $data['content'] . '</textarea>';
        $array['type'] = $data['input_type'];
    
        return $array;
    }
    /**
     * 获取图片域
     * @param array $data
     * @return multitype:string unknown
     */
    private function getImageInput (array $data)
    {
        $array = array();
        $array['name'] = $data['title'];
        $array['input']= $data['content'];
        $array['type'] = $data['input_type'];
        $array['hidden']= '<input type="hidden" name="' . $data['name_id'] . '" id="' . $data['name_id'] . '" value="' . $data['content'] . '" />';
    
        return $array;
    }
    private function getCheckBox (array $data)
    {
        $array = array();
        $array['name']  = $data['title'];
        $array['input'] = '';
        $data['checked']= !is_array($data['checked']) ? array($data['checked']) : $data['checked'];
        foreach ($data['content'] as $value) {
            $array['input'] .= '<label class="checkbox inline"><input type="checkbox" ' .((isset($data['checked']) and is_array($data['checked']) and in_array($value['value'], $data['checked'])) ? 'checked' : ''). ' name="' . $data['name_id'] . '[]" value="' .$value['value']. '" />' .$value['name']. '</label>'; 
        }
        $array['type'] = $data['input_type'];
        
        return $array;
    }
}

?>