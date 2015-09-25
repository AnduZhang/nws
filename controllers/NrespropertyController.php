<?php

namespace app\controllers;

use app\models\UploadForm;
use Yii;
use app\models\NRESProperty;
use app\models\NRESPropertySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use yii\base\Model;
use app\helpers\GoogleAPIHelper;

/**
 * NrespropertyController implements the CRUD actions for NRESProperty model.
 */
class NrespropertyController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [

                        'allow' => true,
                        'roles' => ['@'],

                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
//                    'delete' => ['post'],
//                    'uploadcsv' => ['get','post'],

                ],
            ],
        ];
    }

    /**
     * Lists all NRESProperty models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NRESPropertySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single NRESProperty model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new NRESProperty model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        return $this->renderAjax('_new_property');
    }

    public function actionCreatesingle()
    {

        $model = new NRESProperty(['scenario' => 'create']);
//        if (!\Yii::$app->request->isPost) {
//            $this->performAjaxValidation($model);
//        }


        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $googleHelper = new GoogleAPIHelper();
            $coordinates = $googleHelper->identifyLatLon([$model->streetAddress,$model->city,$model->state,$model->zipcode]);
            if (!$coordinates) {
                echo json_encode(['error'=>'Wrong google coordinates']);
                Yii::$app->end();
            }
            $model->latitude = $coordinates->lat;
            $model->longitude = $coordinates->lng;
            if ($model->save()) {
                Yii::$app->session->setFlash('success','New Property added.');
            } else {
                Yii::$app->session->setFlash('warning','Error while saving property');
            }
            if (Yii::$app->request->isAjax && $model->addNew == 1) {
                Yii::$app->session->getAllFlashes();
                echo json_encode(Yii::$app->session->getAllFlashes());
                Yii::$app->end();
            }

            return $this->redirect(['index']);
//            Yii::$app->end();
        } else {
            if ($model->load(Yii::$app->request->post()) && !$model->validate()) {
                echo json_encode(['errors'=>$model->getErrors()]);
                Yii::$app->end();
            }
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing NRESProperty model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        //$this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success','Property updated.');
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
            ]);
        }
    }


    public function actionUploadcsv()
    {

        $model = new UploadForm();
//        if (Yii::$app->request->isPost) {
//            var_dump($_FILES);die;
//        }
        if ($model->load(Yii::$app->request->post())) {


            $file = UploadedFile::getInstance($model, 'file');
            $model->file = $file->name;


            if ($model->validate()) {
                $file->saveAs(Yii::$app->basePath.'/uploads/' . $file->baseName . '.' . $file->extension);
                $model->processCsv($file);

            }
        }

        return $this->renderAjax('_upload_csv', ['model'=>$model
        ]);
    }
    /**
     * Deletes an existing NRESProperty model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->post()) {
            $this->findModel($id)->delete();
            Yii::$app->session->setFlash('success','Property deleted successfully.');
            return $this->redirect(['index']);
        } else {
            return $this->renderAjax('_delete',['model'=>$model]);
        }



    }

    /**
     * Finds the NRESProperty model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return NRESProperty the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = NRESProperty::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function performAjaxValidation(Model $model)
    {
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $validation = ActiveForm::validate($model);
//            var_dump($validation);die;
//            if ($validation) {
//                echo json_encode($validation);
//                \Yii::$app->end();
//            } else {
//                return true;
//            }
                echo json_encode($validation);
                \Yii::$app->end();
        }
    }
}
