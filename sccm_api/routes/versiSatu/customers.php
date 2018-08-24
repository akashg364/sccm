<?php

return [


 /**
     * @SWG\Post(
     *     path="/v1/customers",
     *     summary="Create Customer",
     *     tags={"Customers"},
     *     description="Description : Create new user to SCCM Portal.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Customer",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Customers"),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Customer",
     *         @SWG\Schema(ref="#/definitions/CustomersCreateResponse")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST customers' => 'customers/create',
    
     /**
     * @SWG\Put(
     *     path="/v1/customers/{id}",
     *     summary="Update Customers",
     *     tags={"Customers"},
      *    description="Description : Update Customer details for given customer id.",
     *     @SWG\Parameter(
     *         ref="#/parameters/id"
     *     ),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Data Customers",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Customers"),
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Data customers",
     *         @SWG\Schema(ref="#/definitions/CustomersUpdate")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'PUT customers/{id}' => 'customers/update',
    
        /**
     * @SWG\Delete(
     *     path="/v1/customers/{id}",
     *     summary="Delete Customer",
     *     tags={"Customers"},
     *     description="Description : Soft delete Customer from SCCM Portal providing customer id.",
     *     @SWG\Parameter(
     *         ref="#/parameters/id"
     *     ),
     *     @SWG\Response(
     *         response=202,
     *         description="Status Delete",
     *         @SWG\Schema(ref="#/definitions/CustomersDelete")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="Resource not found",
     *         @SWG\Schema(ref="#/definitions/Not Found")
     *     )
     * )
     */
    'DELETE customers/{id}' => 'customers/delete',
    
    /**
     * @SWG\Get(
     *     path="/v1/customers/{id}",
     *     summary="Get Customer Data",
     *     tags={"Customers"},
     *     description="Description : Get Customer data by customer id.",
     *     @SWG\Parameter(
     *         ref="#/parameters/id"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Customers",
     *         @SWG\Schema(
     *            type="array",
     *            @SWG\Items(ref="#/definitions/CustomerData")
     *         )
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET customers/{id}' => 'customers/servicedata',
];