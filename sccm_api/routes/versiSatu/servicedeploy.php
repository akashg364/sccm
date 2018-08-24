<?php 

return [
/**
     * @SWG\Post(
     *     path="/v1/servicedeploy/step1",
     *     summary="Service Deploy Step 1",
     *     tags={"Service Deploy"},
     *     description="Description: This API request will initialize the service order in SCCM and return the transaction ID.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Service Deploy",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Servicedeploy1"),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Service Deploy",
     *         @SWG\Schema(ref="#/definitions/Servicedeploy1")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST servicedeploy/step1' => 'servicedeploy/step1',
    
    /**
     * @SWG\Post(
     *     path="/v1/servicedeploy/step2",
     *     summary="Service Deploy Step 2",
     *     tags={"Service Deploy"},
     *     description="Description: This API will supply all details required to create service order in SCCM and return the same transaction id which has been generated in Step1.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Service Deploy",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Servicedeploy2"),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Service Deploy Response",
     *         @SWG\Schema(ref="#/definitions/Servicedeploy2Response")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST servicedeploy/step2' => 'servicedeploy/step2',
    
        /**
     * @SWG\Post(
     *     path="/v1/servicedeploy/step3",
     *     summary="Service Deploy Step 3",
     *     tags={"Service Deploy"},
     *     description="Description: This API will perform service provisioning or scheduling it for later time.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Service Deploy",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Servicedeploy3"),
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Service Deploy",
     *         @SWG\Schema(ref="#/definitions/Servicedeploy3")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST servicedeploy/step3' => 'servicedeploy/step3',
];
