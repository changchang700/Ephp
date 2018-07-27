<?php
namespace Pack;

interface IPack{
    function encode($buffer);

    function decode($buffer);

    function pack($data);

    function unPack($data);

    function getProbufSet();

    function errorHandle($e, $fd);
}