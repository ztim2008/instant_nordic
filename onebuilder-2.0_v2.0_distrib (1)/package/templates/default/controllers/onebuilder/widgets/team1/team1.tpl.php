<div id = <?= $id; ?> class="container">
        <div class="row margin-lg-130t margin-lg-120b margin-lg-60t margin-md-80t margin-md-80b margin-sm-50t margin-sm-0b">
            <div class="col-md-4 margin-lg-10t margin-lg-55b">
                <div class="onebuilder-heading">
                    <<?= $title_preset; ?> class="onebuilder-<?= $title_preset; ?>"><?= $title; ?></<?= $title_preset; ?>>
                    <p class="onebuilder-heading__desc"><?= $content; ?></p>
                </div>
                <div class="margin-lg-45t margin-md-25t"></div>
                <div class="onebuilder-btn-container">
                    <?php
                    switch ($typebutton) {
                    case 'scrollto':
                        echo '<a href="'.$scrolllink.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                    break;
                    case 'linkto':
                        echo '<a href="'.$link.'" target = "_'.$linkopt.'" class="'.$bpreset.'">'.$titlebutton.'</a>';
                    break;
                    case 'none':
                        echo '';
                    break;
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">

                <?php
                    switch ($num_column) {
                    case 'three': echo 
                    
                    '<div class="col-sm-4 margin-sm-35b margin-lg-70b">
                        <div class="onebuilder-member onebuilder-member--home-page t-center">
                            <div class="onebuilder-member__img-holder">
                                <img class="onebuilder-member__img" src="/upload/'.html_image_src($user1, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                            </div>
                            <div class="onebuilder-member__text">
                                <h5 class="onebuilder-member__name">'.$name1.'</h5>
                                <p class="onebuilder-member__position ">'.$work1.'</p>
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="col-sm-4 margin-sm-35b margin-lg-70b">
                        <div class="onebuilder-member onebuilder-member--home-page t-center">
                            <div class="onebuilder-member__img-holder">
                                <img class="onebuilder-member__img" src="/upload/'.html_image_src($user2, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                            </div>
                            <div class="onebuilder-member__text">
                                <h5 class="onebuilder-member__name">'.$name2.'</h5>
                                <p class="onebuilder-member__position ">'.$work2.'</p>
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="col-sm-4 margin-sm-35b margin-lg-70b">
                        <div class="onebuilder-member onebuilder-member--home-page t-center">
                            <div class="onebuilder-member__img-holder">
                                <img class="onebuilder-member__img" src="/upload/'.html_image_src($user3, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                            </div>
                            <div class="onebuilder-member__text">
                                <h5 class="onebuilder-member__name">'.$name3.'</h5>
                                <p class="onebuilder-member__position ">'.$work3.'</p>
                            </div>
                        </div>
                    </div>';
                    break;

                    case 'six':
                        echo 
                        
                        '<div class="col-sm-4 margin-sm-35b margin-lg-70b">
                        <div class="onebuilder-member onebuilder-member--home-page t-center">
                            <div class="onebuilder-member__img-holder">
                                <img class="onebuilder-member__img" src="/upload/'.html_image_src($user1, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                            </div>
                            <div class="onebuilder-member__text">
                                <h5 class="onebuilder-member__name">'.$name1.'</h5>
                                <p class="onebuilder-member__position ">'.$work1.'</p>
                            </div>
                        </div>
                         </div>
                    

                    
                        <div class="col-sm-4 margin-sm-35b margin-lg-70b">
                            <div class="onebuilder-member onebuilder-member--home-page t-center">
                                <div class="onebuilder-member__img-holder">
                                    <img class="onebuilder-member__img" src="/upload/'.html_image_src($user2, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                                </div>
                                <div class="onebuilder-member__text">
                                    <h5 class="onebuilder-member__name">'.$name2.'</h5>
                                    <p class="onebuilder-member__position ">'.$work2.'</p>
                                </div>
                            </div>
                        </div>
                    

                    
                        <div class="col-sm-4 margin-sm-35b margin-lg-70b">
                            <div class="onebuilder-member onebuilder-member--home-page t-center">
                                <div class="onebuilder-member__img-holder">
                                    <img class="onebuilder-member__img" src="/upload/'.html_image_src($user3, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                                </div>
                                <div class="onebuilder-member__text">
                                    <h5 class="onebuilder-member__name">'.$name3.'</h5>
                                    <p class="onebuilder-member__position ">'.$work3.'</p>
                                </div>
                            </div>
                        </div>
                    

                    
                        <div class="col-sm-4 margin-sm-35b">
                            <div class="onebuilder-member onebuilder-member--home-page t-center">
                                <div class="onebuilder-member__img-holder">
                                    <img class="onebuilder-member__img" src="/upload/'.html_image_src($user4, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                                </div>
                                <div class="onebuilder-member__text">
                                    <h5 class="onebuilder-member__name">'.$name4.'</h5>
                                    <p class="onebuilder-member__position ">'.$work4.'</p>
                                </div>
                            </div>
                        </div>
                

                        
                        <div class="col-sm-4 margin-sm-35b">
                            <div class="onebuilder-member onebuilder-member--home-page t-center">
                                <div class="onebuilder-member__img-holder">
                                    <img class="onebuilder-member__img" src="/upload/'.html_image_src($user5, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                                </div>
                                <div class="onebuilder-member__text">
                                    <h5 class="onebuilder-member__name">'.$name5.'</h5>
                                    <p class="onebuilder-member__position ">'.$work5.'</p>
                                </div>
                            </div>
                        </div>
                    
                    
                    
                        <div class="col-sm-4 margin-sm-40b">
                            <div class="onebuilder-member onebuilder-member--home-page t-center">
                                <div class="onebuilder-member__img-holder">
                                    <img class="onebuilder-member__img" src="/upload/'.html_image_src($user6, $size_preset='big', $is_add_host=false, $is_relative=true).'">
                                </div>
                                <div class="onebuilder-member__text">
                                    <h5 class="onebuilder-member__name">'.$name6.'</h5>
                                    <p class="onebuilder-member__position ">'.$work6.'</p>
                                </div>
                            </div>
                        </div>';
                    break;
                    }
                ?>
                
                </div>
            </div>
        </div>
    </div>