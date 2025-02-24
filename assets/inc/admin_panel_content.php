<?php
// Admin panel content
?>
<style>
.container-process {
    max-width: 1520px;
    width: 100%;
    padding-right: 15px;
    padding-left: 15px;
    margin-right: auto;
    margin-left: auto;
}

.step-indicator {
    display: flex;
    align-items: center;
    background: transparent;
    color: white;
    border-radius: 8px;
    padding: 40px 20px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    box-shadow: inset 0px 0px 20px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
    position: relative;
    margin-top: 50px;
    padding-top: 25px;
}

.step {
    display: flex;
    align-items: center;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

.step-indicator .step-icon {
    height: 50px;
    width: 50px;
    border-radius: 50%;
    background: transparent;
    font-size: 20px;
    text-align: center;
    color: #ffffff;
    position: relative;
    line-height: 50px;
    box-shadow: inset 2px 2px 2px 0px rgba(255, 255, 255, .5), 7px 7px 20px 0px rgba(0, 0, 0, .1), 4px 4px 5px 0px rgba(0, 0, 0, .1);
}

.step.active .step-icon {
    background: green;
}

.step p {
    text-align: center;
    position: absolute;
    bottom: -40px;
    color: #c2c2c2;
    font-size: 14px;
    font-weight: bold;
}

.step.active p {
    color: green;
}

.step.step2 p,
.step.step3 p {
    left: 50%;
    transform: translateX(-50%);
}

.indicator-line {
    width: 100%;
    height: 2px;
    background: #c2c2c2;
    flex: 1;
}

.indicator-line.active {
    background: green;
}

.partition {
    display: flex;
    justify-content: space-between;
    gap: 30px;
    width: 100%;
    margin-top: 30px;
}

.left-box {
    flex-grow: 1;
}

.left-box .seo-plugin-data-info.container+.seo-plugin-data-info.container {
    margin-top: 30px;
}

@media screen and (max-width:1799px) {
    .container-process {
        max-width: 1360px;
    }
}

@media screen and (max-width:1599px) {
    .container-process {
        max-width: 1260px;
    }
    .seo-plugin-data-info.container {
        width: 400px;
    }
}

@media screen and (max-width:1499px) {
    .container-process {
        max-width: 1060px;
    }
}

@media screen and (max-width:1299px) {
    .container-process {
        max-width: 960px;
    }
    .seo-plugin-data-info.container {
        width: 350px;
    }
}

@media screen and (max-width:1199px) {
    .container-process {
        max-width: 90%;
    }
    .auto-fold #wpcontent {
        padding-left: 0;
    }
}

@media screen and (max-width:991px) {
    .partition {
        flex-direction: column;
    }
    .seo-plugin-data-info.container,
    .right-box {
        width: 100%;
        margin: 0 auto;
        max-width: 500px;
    }
}

@media screen and (max-width:767px) {
    .seo-plugin-data-info.container,
    .right-box {
        width: auto;
    }
    .step-indicator .step-icon {
        height: 40px;
        width: 40px;
        line-height: 40px;
        font-size: 16px;
    }
    .step p {
        bottom: -35px;
        font-size: 12px;
    }
}

@media screen and (max-width: 500px) {
    .step p {
        font-size: 11px;
        bottom: -20px;
    }
}
</style>

<div class="container-process">
    <div class="step-indicator">
        <div class="step step1 <?php echo ($start_active ? 'active' : ''); ?>">
            <div class="step-icon">1</div>
            <p>START</p>
        </div>
        <div class="indicator-line <?php echo ($start_active ? 'active' : ''); ?>"></div>
        <div class="step step2 <?php echo ($get_active ? 'active' : ''); ?>">
            <div class="step-icon">2</div>
            <p>CHECK</p>
        </div>
        <div class="indicator-line <?php echo ($get_active ? 'active' : ''); ?>"></div>
        <div class="step step3 <?php echo ($set_active ? 'active' : ''); ?>">
            <div class="step-icon">3</div>
            <p>GET</p>
        </div>
        <div class="indicator-line <?php echo ($set_active ? 'active' : ''); ?>"></div>
        <div class="step step4 <?php echo ($upload_active ? 'active' : ''); ?>">
            <div class="step-icon">4</div>
            <p>UPLOAD</p>
        </div>
    </div>

    <section id="processbar" style="display:none;"><span class="loader-71"></span></section>
    <div id="loader" class="lds-dual-ring hidden overlay"></div>

    <div class="partition">
        <div class="left-box">
            <div class="seo-plugin-data-info container api_key_setting_form">
                <div class="inner-content-data">
                    <h2 class="boxtitle">API Key Setting</h2>
                    <form id="api_key_setting_form" method="post" autocomplete="off">
                        <?php wp_nonce_field('review_api_key', 'review_api_key_nonce'); ?>
                        <div class="field_container">
                            <div class="input-field">
                                <input type="text" required id="review_api_key"
                                    data-apiValid="<?php echo esc_attr($get_api_status ? $get_api_status : 0); ?>"
                                    spellcheck="false"
                                    value="<?php echo esc_attr($get_existing_api_key ? $get_existing_api_key : ''); ?>">
                                <label>API Key</label>
                                <span class="correct-sign">✓</span>
                                <span class="wrong-sign">×</span>
                            </div>
                        </div>
                        <div class="twoToneCenter">
                            <button type="submit" class="submit_btn save btn-process">Save</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="seo-plugin-data-info container google_review_upload_form cont hidden">
                <?php if ($firm_data) { ?>
                    <p class="reset new">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 20 20" fill="#fff"
                            xmlns:v="https://vecta.io/nano">
                            <path d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z"/>
                            <path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z"/>
                            <path d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z"/>
                            <path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z"/>
                            <path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z"/>
                        </svg>
                    </p>
                <?php } ?>
                <div class="inner-content-data">
                    <h2 class="boxtitle">Google Reviews Upload</h2>
                    <span class="correct-sign firm_area_sign" style="display:none">✓</span>
                    <span class="wrong-sign firm_area_sign" style="display:none">×</span>
                    <form id="google_review_upload_form" method="post" autocomplete="off">
                        <?php wp_nonce_field('get_set_trigger', 'get_set_trigger_nonce'); ?>
                        <div class="field_container">
                            <div class="input-field">
                                <input <?php echo ($jflag ? 'disabled' : ''); ?> type="text" id="firm_name"
                                    data-termID="<?php echo esc_attr($j_term_id ? $j_term_id : 0); ?>"
                                    data-jobid="<?php echo esc_attr($job_id_data ? $job_id_data : ''); ?>" required
                                    spellcheck="false"
                                    value="<?php echo esc_attr($firm_name_data ? $firm_name_data : ''); ?>">
                                <label>Firm Name</label>
                                <button <?php echo ($jflag ? 'disabled' : ''); ?> type="submit"
                                    class="search_btn <?php echo ($jflag ? 'pointer_none' : ''); ?>">
                                    <span class="material-icons">Search</span>
                                </button>
                            </div>
                            <div class="search-result"></div>
                        </div>
                        <?php
                        $get_d = 0;
                        if ((!empty($getjdata['jobID_json']) && $getjdata['jobID_json'] == 1) && 
                            ($getjdata['jobID_check_status'] == 1) && 
                            ($getjdata['jobID_check'] == 0 && $getjdata['jobID_final'] == 0)) {
                            $get_d = 1;
                        }
                        ?>
                        <div class="submit_btn_setget twoToneCenter">
                            <button type="submit" class="submit_btn job_start btn-process" disabled>
                                <span class="label">JOB START</span>
                            </button>
                            <button type="submit" class="submit_btn check_start_status btn-process" style="display:none;">
                                <span class="label">CHECK STATUS</span>
                            </button>
                            <button type="submit" class="submit_btn check_start btn-process" <?php echo ($jp != 1 ? 'disabled' : '') ?>>
                                <span class="label">GET</span>
                            </button>
                            <button type="submit" class="submit_btn upload_start btn-process">
                                <span class="label">UPLOAD</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="right-box">
            <div class="inner-content-data">
                <h2 class="boxtitle display_total">
                    <p class="reset status">
                        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 20 20" fill="#fff">
                            <path d="M5.05 14.95a1 1 0 0 1 1.414-1.414A4.98 4.98 0 0 0 10 15a5 5 0 0 0 5-5 1 1 0 1 1 2 0 7 7 0 0 1-7 7 6.98 6.98 0 0 1-4.95-2.05z"/>
                            <path d="M13.559 12.832a1 1 0 1 1-1.109-1.664l3-2a1 1 0 1 1 1.109 1.664l-3 2z"/>
                            <path d="M18.832 12.445a1 1 0 1 1-1.664 1.109l-2-3a1 1 0 0 1 1.664-1.109l2 3zm-3.975-7.594a1 1 0 0 1-1.414 1.414 4.98 4.98 0 0 0-3.536-1.464 5 5 0 0 0-5 5 1 1 0 1 1-2 0 7 7 0 0 1 7-7 6.98 6.98 0 0 1 4.95 2.05z"/>
                            <path d="M6.349 6.969a1 1 0 1 1 1.109 1.664l-3 2a1 1 0 1 1-1.109-1.664l3-2z"/>
                            <path d="M1.075 7.356a1 1 0 1 1 1.664-1.109l2 3a1 1 0 1 1-1.664 1.109l-2-3z"/>
                        </svg>
                    </p>
                    Detail Status
                </h2>
                <div class="typewriter">
                    <div class="output typing">
                        <p><?php echo esc_html(displayMessagesFromFile()); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <button class="control" style="display:none;"></button>
        <canvas id="canvas"></canvas>
    </div>
</div> 