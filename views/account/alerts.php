<?php
/**
 * Created by PhpStorm.
 * User: vadim
 * Date: 12.1.15
 * Time: 11.15
 */
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use app\widgets\Alerts\AlertsWidget;
use yii\helpers\BaseUrl;

$this->title = "Alerts View". Yii::$app->params['projectName'];
$updateTime = Yii::$app->params['alertsPageUpdateTime'];
?>

<div class="split">
<div class="split-pane left">
    <?php yii\widgets\Pjax::begin(['id' => 'main-container']) ?>
<!--    --><?php //echo AlertsWidget::widget() ?>
    <div class="expand-fully vertical nudge-left">
        <div class="ef-fix-h">
            <div class="visible-pre-storm">
                <div class="ribbon">
                    <a class="ribbon-inside toggle-alerts toggle-alerts-pre" href="#" data-toggle="pre-storm">
                        <span class="badge badge-green-white count-pre-storm"><?php echo $unreadedPre ?></span>
                        <span class="vsplit"></span>
                        <i class="caret sharp lg"></i>
                        <span class="alert-group-title">Pre-Storm Alerts</span>
                    </a>
                </div>

<!--                <div class="table-caption"><strong>MOST RECENT ALERTS</strong> (165)</div>-->
            </div>
            <div class="visible-post-storm hide">
                <div class="ribbon">
                    <a class="ribbon-inside toggle-alerts toggle-alerts-pre" href="#" data-toggle="pre-storm">
                        <span class="badge badge-green-white count-pre-storm"><?php echo $unreadedPre ?></span>
                        <span class="vsplit"></span>
                        <i class="caret sharp lg"></i>
                        <span class="alert-group-title">Pre-Storm Alerts</span>
                    </a>
                </div>
                <div class="ribbon">
                    <a class="ribbon-inside toggle-alerts toggle-alerts-post" href="#" data-toggle="post-storm">
                        <span class="badge badge-green-white count-post-storm"><?php echo $unreadedPost ?></span>
                        <span class="vsplit"></span>
                        <i class="caret sharp lg"></i>
                        <span class="alert-group-title">Post-Storm Alerts</span>
                    </a>
                </div>

<!--                <div class="table-caption"><strong>MOST RECENT ALERTS</strong> (78)</div>-->
            </div>
        </div>
        <div class="ef-var-h overflow-scroll">
            <div class="visible-pre-storm">
                <div class="filters">
                    <?php yii\widgets\Pjax::begin(['id' => 'pre-alerts-search']) ?>
                    <?php $form = ActiveForm::begin([
                        'action' => ['alerts'],
                        'method' => 'get',
                        'options' => ['data-pjax' => 'true' ],
                    ]); ?>
                    <?php  echo $form->field($searchModelPre, 'event')->dropDownList(['0'=>'Hurricane','1'=>'Tornado']
                        ,['class'=>'','onchange'=>"$.pjax.reload({container:'#pre-alerts', timeout: 10000,url: $('#pre-alerts-search form').attr('action') + '?' + $('#pre-alerts-search form').serialize()})",'prompt'=>'Storm Type: All'])->label(false) ?>

                    <?php ActiveForm::end(); ?>
                    <?php yii\widgets\Pjax::end() ?>
                </div>

                <div class="data-index">
                    <?php Pjax::begin(['id' => 'pre-alerts']) ?>
                    <div class="table-caption"><strong>MOST RECENT ALERTS</strong> (<?= $dataProviderPre->totalCount ?>)
                        <div class="pre-update-time">Updated: <?php echo date('M d, Y H:i:s') ?></div>
                    </div>
                        <?= $this->render('_pre_storm',['searchModelPre'=>$searchModelPre,'dataProviderPre'=>$dataProviderPre]); ?>
                    <?php Pjax::end() ?>
                </div>
            </div>
            <div class="visible-post-storm hide">
                <div class="data-index">
                <div class="filters">
                    <?php yii\widgets\Pjax::begin(['id' => 'post-alerts-search']) ?>
                    <?php $form = ActiveForm::begin([
                        'action' => ['alerts'],
                        'id'=>'post-alerts-filter',
                        'method' => 'get',
                        'options' => ['data-pjax' => 'true' ],
                    ]); ?>
                    <?php  echo $form->field($searchModelPre, 'event')->dropDownList(['0'=>'Hurricane','1'=>'Tornado']
                        ,['class'=>'','onchange'=>"$.pjax.reload({container:'#post-alerts', timeout: 10000,url: $('#post-alerts-search form').attr('action') + '?' + $('#post-alerts-search form').serialize()})",'prompt'=>'Storm Type: All'])->label(false) ?>

                    <?php ActiveForm::end(); ?>
                    <?php yii\widgets\Pjax::end() ?>
                </div>
                    <?php Pjax::begin(['id' => 'post-alerts']) ?>
                                    <div class="table-caption"><strong>MOST RECENT ALERTS</strong> (<?= $dataProviderPost->totalCount ?>)
                                        <div class="pre-update-time">Updated: <?php echo date('M d, Y H:i:s') ?></div>
                                    </div>
                        <?= $this->render('_post_storm',['searchModelPost'=>$searchModelPost,'dataProviderPost'=>$dataProviderPost]); ?>
                    <?php Pjax::end() ?>
                </div>
            </div>
        </div>
        <div class="ef-fix-h">
            <div class="visible-pre-storm">
                <div class="ribbon">
                    <a class="ribbon-inside toggle-alerts collapsed toggle-alerts-post" href="#" data-toggle="post-storm">
                        <span class="badge badge-green-white count-post-storm"><?php echo $unreadedPost ?></span>
                        <span class="vsplit"></span>
                        <i class="caret sharp lg"></i>
                        <span class="alert-group-title">Post-Storm Alerts</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php yii\widgets\Pjax::end() ?>
</div>

<div class="split-frame"></div>
<div class="split-pane right">
        <?php echo AlertsWidget::widget() ?>
</div>
</div>

<?php
$script = <<< JS
function updateStormInformation(row) {
    if ($('.choose-overlay').length)
    $('.choose-overlay').animate({opacity:0},500);

    setTimeout(function(){jQuery('.choose-overlay').remove()},500);



    var id = row.id;
    gmap.activeId = id;

    var makeRead = false;
    if ($(row).hasClass('new-alert-pre') || $(row).hasClass('new-alert-post')) {

        updateCount($(row).attr('class'));
        makeRead = true;
        $(row).removeAttr('class');

    }

    //if ($(row).hasClass('pre-alert')) {
    //    gmap.activeTab = 0;
    //} else {
    //    gmap.activeTab = 1;
    //}

    //return;

    var tableId = $(row).parent().parent().parent()[0].getAttribute('id');
    $('.table tr').removeClass('active');
    $('#' + tableId+ ' tr').removeClass('active');

    $(row).addClass('active');
//alert('ID is ' + id);
    $('.storm-properties .sp-a div.sp-name').text('Alert #' + id);
//Need to get information from the database about selected storm

            $.ajax({
            url: "index.php?r=alerts/getalertinformation",
            type: 'post',
            data: {'id':gmap.activeId,'makeRead':makeRead},

            success: function(data) {

                var jsonData = JSON.parse(data);

                if (!jsonData || jsonData.error) {
                    nresAjax.showFlashMessage('danger','Alert information is not found.');
                    return false;
                }
                //if (jsonData.alert.type===1) {
                //
                //    if (!jsonData.coordinates[0].radius) {
                //        jsonData.coordinates[0].radius = 1;// Hardcoded yet
                //    }
                //}
                if (jsonData.alert.magnitude===null) {
                    jsonData.alert.magnitude = 0;
                }
                $('.radius-span').remove();

                if (jsonData.alert.magnitude>0 || jsonData.alert.type===1) {
                    magnitudeString = jsonData.alert.magnitude;

                    if (jsonData.alert.magnitudeUnit) {
                    magnitudeString+= ' ' + jsonData.alert.magnitudeUnit.toLowerCase();
                    }
                    $('.storm-properties .sp-c span:eq(0)').html('Magnitude: <span class="red-span">' + magnitudeString + '</span>');
                    $('.sp-c').append('<span class="text-muted radius-span">Radius: <span class="radius">' + jsonData.coordinates[0].radius + ' km</span></span>');
                } else {

                    $('.storm-properties .sp-c span:eq(0)').html('Severity: <span>' + jsonData.alert.severity +'</span>');
                }

                $('.storm-properties .sp-c span:eq(2) span').text(jsonData.generalInformation.location);
                gmap.json = jsonData;
                changeStormIcon();


                if (gmap.currentView) {
                    displayList(makeRead);
                } else {
                    displayMap(makeRead);
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
                alert('An Error occurred. (' + thrownError + ')');
            }
        });

}

    function displayList(makeRead) {
            $("#affected-list").show(0);
            $("#map-canvas").hide(0);
            $.ajax({
            url: "index.php?r=alerts/affectedpropertieslist&id=" + gmap.activeId + '&makeRead=' + makeRead,
            type: 'get',

            success: function(data) {
                $('#affected-list').html(data);
                if (gmap.currentAffectedFilter) { //Update detected
                    $('#nrespropertysearch-client').val(gmap.currentAffectedFilter).change();
                    gmap.currentAffectedFilter = null; // reset value
                }
            }
            ,
            error: function (xhr, ajaxOptions, thrownError) {
                alert('An Error occurred. (' + thrownError + ')');
            }
        });


    }

    function displayMap(makeRead) {
            gmap.initialize();
            $("#affected-list").hide(0);
            $("#map-canvas").show(0);
    }


    function updateCount(rowClass) {
        var selector = '.count-pre-storm';
        if (rowClass=='new-alert-post') {
            var selector = '.count-post-storm';
        }

        var newCount = parseInt($(selector+':eq(1)').text()) - 1;
        $(selector).html(newCount);
        //$('.count-post-storm').text($('.new-alert-post').length);
    }

    function gridsUpdate() {
        gmap.currentAffectedFilter = $('#nrespropertysearch-client option:selected').val();

        //var currentSelectedId = gmap.activeId;
        $.pjax.reload({container:'#main-container',custom_success:function(){
            $('#' + gmap.activeId).addClass('active');
            if (gmap.activeTab) {
            $('.visible-pre-storm').addClass('hide');
            $('.visible-post-storm').removeClass('hide');
            }
            $('.pre-update-time').animate({
                opacity:0.1
            },1).animate({
                opacity:1
            },1200);
        },timeout:10000
        });
    }

    function changeStormIcon() {

        var classToAdd = null;
        switch (gmap.json.alert.event) {
        case 0:
            classToAdd = 'hurricane';
            break;
        case 1:
            classToAdd = 'tornado';
            break;
        default:
            classToAdd = 'other';
        }
        $('.ico-storm').removeClass('storm').removeClass('hurricane').removeClass('other').addClass(classToAdd);
    }
JS;
$this->registerJs($script, \yii\web\View::POS_HEAD);

$this->registerJs("
    resize();
     setTimeout(function() {
         gridsUpdate();
     },".$updateTime.");

     $(document).on('pjax:success', function(event, data, status, xhr, options) {
      if(typeof options.custom_success === 'function'){
           options.custom_success();
           setTimeout(function() {
            gridsUpdate()
           },".$updateTime.");

      }
 });

    $(document).on('click','#map_view',function(){


        gmap.currentView = 0;
        $('.btn-geyser').removeClass('on');
        $(this).addClass('on');
//        $('#' + gmap.activeId).click();
        displayMap(false);
        gmap.initialize();


    });

    $(document).on('click','#list_view',function(){
        gmap.currentView = 1;
        $('.btn-geyser').removeClass('on');
        $(this).addClass('on');
        displayList(false);
    });

    $(document).on('click','#csv_export',function(){
                    $.ajax({
            url: 'index.php?r=alerts/exportcsv',
            type: 'post',
            data: {'alertId':gmap.activeId},

            success: function(data) {

                var response = JSON.parse(data);
                if (typeof(response.error)!=='undefined') {
                    alert(response.error); return false;
                }
                if (typeof(response.file)!=='undefined') {
                    document.location.href = '".BaseUrl::base(true)."/index.php?r=alerts/exportcsv&file=' + response.file;
                }

            }
        });

    });

        $(document).on('click','#pdf_export',function(){
                    $.ajax({
            url: 'index.php?r=alerts/exportpdf',
            type: 'post',
            data: {'alertId':gmap.activeId},

            success: function(data) {

                var response = JSON.parse(data);
                if (typeof(response.error)!=='undefined') {
                    alert(response.error); return false;
                }
                if (typeof(response.file)!=='undefined') {
                    document.location.href = '".BaseUrl::base(true)."/index.php?r=alerts/exportpdf&file=' + response.file;
                }

            }
        });

    });

    $(window).resize(function(){
        resize();
    });

    function resize() {
        $('.sp-c').outerWidth($('.storm-properties').outerWidth() - $('.sp-a').outerWidth() - $('.sp-b').outerWidth() - 15);
    }

    $(document).on('click','.toggle-alerts', function() {
        if ($(this).hasClass('toggle-alerts-post')) {
            gmap.activeTab = 1;
        } else {
            gmap.activeTab = 0;
        }
    });

", \yii\web\View::POS_READY);
?>
