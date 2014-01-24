<?php

namespace Slim\Bootstrap;

/**
 * DTO基底クラス
 */
class DTOBase {

	protected $data = array(); // 実データ配列

	protected $map = array(); // マッピング設定

	protected $callbacks = array( // コールバック設定
		'createdAt' => 'isodate',
		'updatedAt' => 'isodate'
	);

	protected $filter = null;

	/**
	 * プロパティのセット
	 * keyをmapで指定された値かキャメルケースに変換
	 * valueを指定されたコールバックで変換して$this->dataに保存
	 */
	public function __set( $name, $val ) {
		if (array_key_exists($name,$this->map)) {
			$key = $this->map[$name];
		} else {
			$key = Util::to_camelcase($name);
		}

		if (array_key_exists($key, $this->callbacks)) {
			$callback = $this->callbacks[$key];
			$val = $this->$callback($val);
		}
		$this->data[$key] = $val;
	}

	/**
	 * プロパティの取得
	 * 指定するプロパティ名はキャメルケースでもスネークケースでもいい
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->map)) {
			$name = $this->map[$name];
		}
		if (strpos($name,'_')) {
			$key = Util::to_camelcase($name);
		} else {
			$key = $name;
		}
		return (isset($this->data[$key])) ? $this->data[$key] : null;
	}

	/**
	 * プロパティの変数がセットされていること、そして NULL でないことを検査する
	 * 指定するプロパティ名はキャメルケースでもスネークケースでもいい
	 */
	public function __isset($name) {
		if (array_key_exists($name, $this->map)) {
			$name = $this->map[$name];
		}
		if (strpos($name,'_')) {
			$key = Util::to_camelcase($name);
		} else {
			$key = $name;
		}
		return isset($this->data[$key]);
	}

	/**
	 * 文字列に変換された場合に基底クラスの名前を返すように
	 */
	public function __toString() {
		$class = explode('\\', __CLASS__);
		return end($class);
	}

	/**
	 * jsonに変換
	 */
	public function to_json() {
		$data = $this->get_data();
		$this->filter = null;
		return json_encode($data);
	}

	/**
	 * 配列に変換
	 */
	public function to_array() {
		$data = $this->get_data();
		$this->filter = null;
		return $data;
	}

	/**
	 * 出力のフィルタリングをセット
	 */
	public function filter($filter) {
		if (is_array($filter)) {
			$this->filter = array_map(array('LL\Lib\Util','to_camelcase'), $filter);
		}
		return $this;
	}

	/**
	 * フィルタリングしたデータ配列を返す
	 */
	protected function get_data() {
		if (!is_null($this->filter) && is_array($this->filter)) {
			$data = array();
			foreach ($this->data as $key => $value) {
				if (in_array($key, $this->filter)) {
					$data[$key] = $value;
				}
			}
			return $data;
		} else {
			return $this->data;
		}
	}


	/*****************************************
	 * callbacks
	 ****************************************/

	protected function to_boolean($val) {

		if((int)$val==0){
			$val = false;
		}else{
			$val = true;
		}
		return $val;
	}

	protected function to_int($val){

		return (int)$val;
	}

	/* callbacks */
	protected function isodate($val) {
		// return date('c', strtotime($val));
		$date = new \DateTime($val);
		return $date->format(\DateTime::ISO8601);
	}

}
