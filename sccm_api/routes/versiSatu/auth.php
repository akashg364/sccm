<?php

return [
    /**
     * @SWG\Post(
     *     path="/v1/login",
     *     summary="Login to the SCCM App",
     *     tags={"Auth"},
     *     description="Description : Login to SCCM App for fetching Access Token.",
     *     @SWG\Parameter(
     *         name="username",
     *         in="formData",
     *         type="string",
     *         description="Your Username",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="password",
     *         in="formData",
     *         type="string",
     *         description="Your Password",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="User Details after user gets login in to the app",
     *         @SWG\Schema(ref="#/definitions/LoginForm")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST login' => 'guest/login',
    /**
     * @SWG\Post(
     *     path="/v1/register",
     *     summary="Register a new User",
     *     tags={"Auth"},
     *     description="Description : Register new user to SCCM Portal.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Data Register",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/NewUser"),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Data user",
     *         @SWG\Schema(ref="#/definitions/LoginForm")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST register' => 'guest/register',
    /**
     * @SWG\Get(
     *   path="/v1/me",
     *   summary="Get current logged in user",
     *   tags={"Auth"},
     *   description="Description : Get current logged in user to SCCM Portal.",
     *   @SWG\Response(
     *     response=200,
     *     description="Data user",
     *     @SWG\Schema(ref="#/definitions/CurrentUser")
     *   ),
     *   @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @SWG\Schema(ref="#/definitions/Unauthorized")
     *   )
     * )
     */
    'GET me' => 'auth/me',
    
];