<?php

return [

    /**
     * @SWG\Get(
     *     path="/v1/services",
     *     summary="Get List of Services",
     *     tags={"Services"},
     *     description="Description: This API request will return list of services and its variants details.", 
     *     @SWG\Response(
     *         response=200,
     *         description="Services List",
     *        @SWG\Schema(ref="#/definitions/ServicesListResponse")
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET services' => 'services/index',

     /**
     * @SWG\Get(
     *     path="/v1/services/{id}",
     *     summary="Get Service by ID",
     *     tags={"Services"},
     *     description="Description: The API request will return all Sub Services details and their User Define (UD) and System Define (SD) parameters.", 
     *     @SWG\Parameter(
     *         ref="#/parameters/id"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Data services",
     *         @SWG\Schema(ref="#/definitions/ServiceById")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="Resource not found",
     *         @SWG\Schema(ref="#/definitions/Not Found")
     *     )
     * )
     */
    'GET services/{id}' => 'services/view',

    /**
     * @SWG\Post(
     *     path="/v1/deletefullservice",
     *     summary="Delete a full Service",
     *     tags={"Services"},
     *     description="Description: This API request will delete/undeploy a existing service from the circuit based on service order id.", 
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Delete full service from NSO and SCCM",
     *         required=true,
     *             @SWG\Schema(ref="#/definitions/DeleteFullService"),
     *     ),
      @SWG\Response(
     *         response=200,
     *         description="Services Delete Full Service",
     *         @SWG\Schema(ref="#/definitions/ServiceDelete")
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'POST deletefullservice' => 'services/deletefullservice',
    
    /**
     * @SWG\Get(
     *     path="/v1/customer_services",
     *     summary="Get Customer Services",
     *     tags={"Services"},
     *     description="Description: This API request will fetch all the customers and its related services.", 
     *     @SWG\Response(
     *         response=200,
     *         description="Services",
     *         @SWG\Schema(
     *            type="array",
     *            @SWG\Items(ref="#/definitions/CustomerServices")
     *         )
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET customer_services' => 'services/customerservices',
];