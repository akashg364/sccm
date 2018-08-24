<?php

use backend\models\ServiceModel;

/* @var $this yii\web\View */

$this->title = ucfirst($user->user_type).' Dashboard';
?>
<style>
    /** styles for dashboard starts here ***/
    .dashboard .list {
        height: 352px;
        font-weight: 300;
        text-transform: none;
        height: 380px;
        overflow-y: scroll;
    }
    /** styles for dashboard ends here ***/
</style>
<div class="site-index dashboard">
    <div class="body-content">
        <p></p>
        <div class="row">

            <div class="col-sm-6">
                <div class="form-control list" data-target="available">
                    <h4 style="padding-bottom: 6px;font-weight: bold">Requested Approval for </h4>
                    <?php if (!empty($users)) { ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Company Name</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = 1;
                                foreach ($users as $user) {

                                    echo '<tr><td>' . $counter . '</td><td>' . $user->company_name . '</td><td> ' . ServiceModel::truncate($user->description, 88) . '</td></tr>';

                                    $counter++;
                                }
                                ?>

                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>
            
            <div class="col-sm-6">

                <div class="form-control list" data-target="available">
                    <h4 style="padding-bottom: 6px;font-weight: bold">Services</h4>
                    <?php if (!empty($services)) { ?>
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Service Name</th>
                                    <th>Sub Service Name</th>
                                    <th>Topology Name</th>
                                </tr>
                            </thead><tbody>
                                <?php
                                $counter = 1;
                                foreach ($services as $service) {
                                    if(!isset($service->subService)){
                                        continue;
                                    }
                                   echo '<tr><td>' . $counter . '</td><td>' . $service->service->name . '</td><td>' . $service->name . '</td><td>' . $service->subService->name . '</td><td> ' . ServiceModel::truncate($service->topology->name, 88) . '</td></tr>';
                                    $counter++;
                                }
                                ?>

                            </tbody>
                        </table>
                    <?php } ?>
                </div>

            </div>
        </div>
    </div>
</div>