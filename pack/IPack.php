<?php

namespace Pack;

interface IPack {

	function encode($buffer);

	function decode($buffer);

	function pack($data);

	function unPack($data);

	function getPackSet();

	function errorHandle($e, $fd);
}
