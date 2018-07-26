<?php
namespace Route;

interface IRoute{
    function handleClientData($data);

    function handleClientRequest($request);

    function getControllerName();

    function getMethodName();

    function errorHandle(\Exception $e, $fd);

    function errorHttpHandle(\Exception $e, $request, $response);

}