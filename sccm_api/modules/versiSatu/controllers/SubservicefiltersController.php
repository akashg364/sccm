<?php

namespace app\modules\versiSatu\controllers;

use app\components\Controller;
use app\models\Subservicefilters;

class SubservicefiltersController extends Controller {


	public function actionIndex() {
		$data = [];
		try {
			$filters = SubServiceFilters::find()->all();

			foreach ($filters as $key => $value) {
				$data[$value["type"]][$value["param"]] = $value["value"];
			}
			return $this->apiResponse(200, 200, 2000, $data, $message = "Success");
		} catch (\Exception $ex) {
			$error = $ex->getMessage();
			return $this->apiResponse(200, 300, 3000, $data, $message = "", $error);
		}

	}

}