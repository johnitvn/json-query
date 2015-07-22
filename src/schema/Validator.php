<?php

namespace johnitvn\jsonquery\schema;

/**
 * @author John Martin <john.itvn@gmail.com>
 * @since 1.0.0
 */
class Validator {

    public $error;

    public function check($data, $model, $lax = false) {
        $result = true;
        $this->error = '';
        $constraints = new Constraints($lax);

        try {
            $constraints->validate($data, $model->data);
        } catch (ValidationException $e) {
            $this->error = $e->getMessage();
            $result = false;
        }

        return $result;
    }

}
