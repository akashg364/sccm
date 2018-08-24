<?php

return [
    /**
     * @SWG\Get(
     *     path="/v1/device/find-devices",
     *     summary="Get Devices by latlong/pincode",
     *     tags={"Devices"},
     *     description="Description : Get device details by given latlong or pincode.",
     *     @SWG\Parameter(
     *         name="latitude",
     *         in="query",
     *         description="Latitude",
     *         type="number",
     *         format="float", 
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/Device"),
     *     ),
     *     @SWG\Parameter(
     *         name="longitude",
     *         in="query",
     *         description="Longitude",
     *         type="string", 
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/Device")
     *     ),
     *     @SWG\Parameter(
     *         name="radius",
     *         in="query",
     *         description="Radius",
     *         type="string", 
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/Device")
     *     ),
     *     @SWG\Parameter(
     *         name="pincode",
     *         in="query",
     *         description="Pincode",
     *         type="string", 
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/Device")
     *     ),  
     *     @SWG\Parameter(
     *         name="device_type",
     *         in="query",
     *         description="Device TYpe",
     *         type="string", 
     *         required=false,
     *         @SWG\Schema(ref="#/definitions/Device")
     *     ),          
     *     @SWG\Response(
     *         response=200,
     *         description="Device details",
     *         @SWG\Schema(ref="#/definitions/DeviceByLocation")
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET device/find-devices' => 'device/find-devices',
    /**
     * @SWG\Get(
     *     path="/v1/device",
     *     summary="Device details by neid",
     *     tags={"Devices"},
     *     description="Description : Get Device details by given neid.",
     *     @SWG\Parameter(
     *         name="neid",
     *         in="query",
     *         description="Neid",
     *         type="string", 
     *         required=true
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Device Details by NEID",
     *         @SWG\Schema(ref="#/definitions/DeviceByNeid")
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET device' => 'device/get-device-by-neid',

];