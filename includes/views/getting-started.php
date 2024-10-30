<?php

require_once 'getting-started-data.php';
wp_enqueue_style( 'indbox-getting-started-css' );
wp_enqueue_script( 'indbox-getting-started-script' );
?>
<!-- Dropbox GT Page Hero Start -->
<section class="dropbox-getting-hero">
    <div class="container">
        <div class="gt-content">
            <div class="getting-logo flex-center">
                <div class="icon flex-center">
                    <img src="<?php echo esc_url( INDBOX_ASSETS . '/admin/getting-started/images/dropbox-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Integrating Dropbox' );?>" />
                </div>
                <h3 class="title text-center"><?php esc_html_e( 'Integrate Dropbox', 'integrate-dropbox' );?></h3>
            </div>
            <div class="getting-title text-center">
                <h2>
                    <?php
printf(
    'Power Up Your <span>%s</span> With <span>%s</span> Cloud',
    esc_html__( 'WordPress' ),
    esc_html__( 'Dropbox' )
);
?>
                </h2>
                <h5>
                    <?php esc_html_e(
    ' Make your website cooler with the Integrate Dropbox
                                Plugin for a stylish and user-friendly experience.
                                Make your website cooler with the Integrate Dropbox
                                Plugin.',
    'integrate-dropbox'
);
?>
                </h5>
            </div>
            <?php
global $indbox_fs;
if ( ! $indbox_fs->is_paying() ) {?>
                <div class="getting-hero-btn">
                    <a class="f-btn" href="<?php echo esc_url( $indbox_fs->get_upgrade_url() ); ?>">Upgrade Pro</a>
                </div>
            <?php
} else {
    ?>

                <div class="getting-hero-btn">
                    <a class="f-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=integrate-dropbox-settings' ) ); ?>">Settings</a>
                </div>

            <?php
}

?>

        </div>
    </div>
</section>
<!-- Dropbox GT Page Hero End -->

<!-- Dropbox GT Page Feature's Tab Start -->
<?php $urlParams = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : null;?>
<section class="getting-tab-section white-bg">
    <div class="container">
        <div class="gt-content">
            <!-- Tab Menu Bar Start -->
            <div class="getting-tab-menu">
                <ul class="tab-wrapper d-flex space-between unstyle">
                    <?php
if ( ! empty( $tab_menu_options ) && is_array( $tab_menu_options ) ):

    foreach ( $tab_menu_options as $key => $tab_menu_option ):
        $menu_logo = isset( $tab_menu_option['logo'] ) ? $tab_menu_option['logo'] : '';
        $menu_name = isset( $tab_menu_option['tab_name'] ) ? $tab_menu_option['tab_name'] : '';
        $formate_menu_name = strtolower( str_replace( ' ', '-', $menu_name ) );
        $activeClass = $formate_menu_name === $urlParams ? ' tab-active' : '';
        if ( empty( $activeClass ) && ! $urlParams ) {
            $activeClass = $key == 0 ? ' tab-active' : '';
        }
        ?>
		                            <li class="tabs p-relative tab-<?php echo strtolower( str_replace( ' ', '-', $menu_name ) );
        echo $activeClass; ?>" data-tab="<?php echo strtolower( str_replace( ' ', '-', $menu_name ) ); ?>">
		                                <span tab-name="<?php echo esc_attr( $menu_name ); ?>" class="tabs-meta flex-center flex-col">
		                                    <div class="icon flex-center transition">
		                                        <img src="<?php echo esc_url( INDBOX_ASSETS . "/admin/getting-started/images/{$menu_logo}" ); ?>" alt="<?php echo esc_attr( $menu_name ); ?>" />
		                                    </div>
		                                    <p><?php echo esc_html( $menu_name ); ?></p>
		                                </span>
		                            </li>
		                    <?php endforeach;
endif;?>
                </ul>

            </div>
            <!-- Tab Menu Bar End -->
        </div>
    </div>
</section>
<!-- Dropbox GT Page Feature's Tab End -->

<!-- Dropbox GT Page Tab Feature's Start -->
<section class="getting-tab-features white-bg tab-content section-introduction<?php echo ( $urlParams === 'introduction' || empty( $urlParams ) ) ? ' section-active' : '' ?>" tab-name="Introduction">
    <div class="container">
        <div class="gt-content">
            <!-- Feature's Start -->
            <div class="db-overview">
                <div class="overview-introduction">
                    <div class="section-title">
                        <h2><?php esc_html_e( 'Quick Overview', 'integrate-dropbox' );?></h2>
                        <p>
                            <?php esc_html_e( 'Experience seamless integration between Dropbox and WordPress with the most user-friendly Dropbox plugin for WordPress. Effortlessly manage and share your Dropbox documents and media files directly on your website. With just a few clicks, you can browse files, create galleries, slider, Media player, and display content without any coding hassles.', 'integrate-dropbox' );?>
                        </p>
                    </div>
                    <div class="cc-iframe-responsive">

                        <iframe width="560" height="315" src="https://www.youtube.com/embed/5Ee-sQ9p7kA?si=xnTI7tnIKIBjixu1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="overview-features">
                    <h2 class="fe-heading text-center">
                        <?php esc_html_e( 'Main Features', 'integrate-dropbox' );?>
                    </h2>
                    <div class="features-wrapper d-flex flex-wrap">
                        <?php
if ( ! empty( $feature_cards ) && is_array( $feature_cards ) ):
    foreach ( $feature_cards as $feature_card ):
        $feature_img = isset( $feature_card['image'] ) ? $feature_card['image'] : '';
        $feature_meta = isset( $feature_card['meta'] ) ? $feature_card['meta'] : '';
        $feature_description = isset( $feature_card['description'] ) ? $feature_card['description'] : '';
        $coming_feature = isset( $feature_card['isComing'] ) ? 'coming-soon' : '';
        ?>
		                                <div class="feature-card flex-center flex-col <?php echo esc_attr( $coming_feature ); ?>">
		                                    <?php if ( $coming_feature ) {
            printf( '<span class="coming-soon">Coming Soon </span>' );
        }?>
		                                    <div class="feature-image p-relative">
		                                        <img src="<?php echo esc_url( INDBOX_ASSETS . "/admin/getting-started/images/feature-images/{$feature_img}" ); ?>" alt="<?php echo esc_attr( $feature_meta ); ?>" />
		                                        <div class="solar-circle">
		                                            <span></span>
		                                            <span></span>
		                                            <span></span>
		                                            <span></span>
		                                        </div>
		                                    </div>

		                                    <div class="feature-meta text-center p-relative">
		                                        <h3><?php echo esc_html( $feature_meta ); ?></h3>
		                                        <p>
		                                            <?php echo esc_html( $feature_description ); ?>
		                                        </p>
		                                    </div>
		                                </div>
		                        <?php endforeach;
endif;?>
                    </div>
                </div>

                <div class="exp-more-btn text-center">
                    <a class="f-btn" target="_blank" href="https://codeconfig.dev/integrate-dropbox/">View Details</a>
                </div>

                <div class="db-explore-feature flex-center flex-col">
                    <h2 class="fe-heading text-center">
                        <?php esc_html_e( 'Explore Every Single Feature', 'integrate-dropbox' );?>
                    </h2>
                    <div class="explore-feature-wrapper flex-center">
                        <?php
if ( ! empty( $explore_features ) && is_array( $explore_features ) ):
    foreach ( $explore_features as $explore_feature ):
        $explore_img = $explore_feature['image'];
        $explore_title = $explore_feature['title'];
        $explore_description = $explore_feature['description'];

        ?>

		                                <div class="single-fe-card">
		                                    <div class="card-logo p-relative">
		                                        <div class="features-logo-bg p-relative">
		                                            <img src="<?php echo esc_url( INDBOX_ASSETS . "/admin/getting-started/images/{$explore_img}" ); ?>" alt="<?php esc_attr( $explore_title );?>" />
		                                        </div>
		                                    </div>

		                                    <div class="fe-meta text-center">
		                                        <h5><?php echo esc_html( $explore_title ); ?></h5>
		                                        <p>
		                                            <?php echo esc_html( $explore_description ); ?>
		                                        </p>
		                                    </div>
		                                </div>
		                        <?php endforeach;
endif;?>
                    </div>
                    <div class="exp-more-btn">
                        <a class="f-btn" target="_blank" href="<?php echo esc_url( 'https://codeconfig.dev/integrate-dropbox/' ) ?>">More Features</a>
                    </div>
                </div>
            </div>
            <!-- Feature's End -->
        </div>
    </div>
</section>
<!-- Dropbox GT Page Tab Feature's End -->
<?php
if ( ! empty( $tab_menu_options ) && is_array( $tab_menu_options ) ):
    foreach ( $tab_menu_options as $key => $tab_feature_section ):

        $section_tab_name = isset( $tab_feature_section['tab_name'] ) ? $tab_feature_section['tab_name'] : '';
        $tab_item = isset($tab_feature_actions[$section_tab_name]) ? $tab_feature_actions[$section_tab_name] : null;
        $is_need_help = isset($tab_item['need_help']) ? $tab_item['need_help'] : null;
        ?>
		        <!-- Dropbox GT Page Tab Area Start -->
		        <?php
        if ( ! empty( $tab_item ) && is_array( $tab_item ) ):

            $section_title = isset($tab_item['section_title'] ) ? $tab_item['section_title'] : '';
            $section_sub_title = isset( $tab_item['subtitle']) ?  $tab_item['subtitle'] : '';
            $section_content = isset($tab_item['content']) ? $tab_item['content'] : null;

            $formate_menu_name = strtolower( str_replace( ' ', '-', $section_tab_name ) );
            $activeClass = $formate_menu_name === $urlParams ? ' section-active' : '';
            ?>
			            <section class="getting-user-manual white-bg tab-content section-<?php echo $formate_menu_name;
            echo empty( $is_need_help ) ? '' : ' faq-page';
            echo $activeClass; ?>" tab-name="<?php echo esc_attr( $section_tab_name ); ?>">
			                <div class="container">
			                    <div class="gt-content">
			                        <!-- user manual Start -->

			                        <div class="section-title">
			                            <h2><?php echo esc_html( $section_title ); ?></h2>
			                            <p>
			                                <?php echo esc_html( $section_sub_title ); ?>
			                            </p>
			                        </div>

			                        <?php
            $class_log = '';
            $class_description = '';
            if ( $section_tab_name === 'Changelog' ) {
                $class_log = 'change-log';
                $class_description = 'version-description';
            }
            ?>

			                        <div class="gt-manual">
			                            <div class="user-manual-wrapper d-flex flex-col <?php echo esc_attr( $class_log ); ?>">
			                                <?php
            if ( ! empty( $section_content ) && is_array( $section_content ) ):
                foreach ( $section_content as $key => $user_manual ):
                    $manual_title = isset($user_manual['title']) ? $user_manual['title'] : '';
                    $manual_description = isset($user_manual['description']) ? $user_manual['description'] : '';
                    $manual_media = isset( $user_manual['media']) ?  $user_manual['media'] : '';
                    ?>
					                                        <div class="user-manuals d-flex flex-col<?php echo $key === 0 ? ' answer-slideshow' : ''; ?>">
					                                            <div class="manual-header d-flex cursor-pointer">
					                                                <div class="indbox-toggle"></div>
					                                                <h5>

					                                                    <?php if ( $section_tab_name == 'Changelog' ):

                        $ver_no = isset($user_manual['version_no']) ? $user_manual['version_no'] : '';
                        $ver_type = isset( $user_manual['version_type'] ) ?  $user_manual['version_type'] : '';
                    else:
                        $ver_no = null;
                        $ver_type = null;
                    endif;
                    ?>
					                                                    <span class="version"><?php echo esc_html( $ver_no ); ?></span>

					                                                    <?php echo esc_html( $manual_title ); ?>

					                                                    <span class="re-version"><?php echo esc_html( $ver_type ); ?></span>
					                                                </h5>
					                                            </div>
					                                            <div class="manual-content" style="display: <?php echo $key === 0 ? 'block' : 'none'; ?>">

					                                                <article class="content-meta <?php echo esc_attr( $class_description ); ?>">

					                                                    <?php echo wp_kses_post( $manual_description ); ?>

					                                                    <?php if ( $section_tab_name === 'Changelog' ):

                        $log_details = isset($user_manual['log_details']) ? $user_manual['log_details'] : [];
                        foreach ( $log_details as $log ):
                            $statusClass = '';

                            switch ( $log['title'] ) {
                            case "What's New":
                                $statusClass = 'whats-new';
                                break;
                            case 'Updated Features':
                                $statusClass = 'update-features';
                                break;
                            default:
                                $statusClass = 'fixed-issues';
                                break;
                            }
                            ?>
							                                                    <h4 class="<?php echo esc_attr( $statusClass ); ?>"><?php echo esc_html( $log['title'] ); ?><span></span></h4>
							                                                    <ul class="ver-fixed">
							                                                        <?php foreach ( $log['logs'] as $item ): ?>
							                                                            <li><?php echo esc_html( $item ); ?></li>
							                                                        <?php endforeach;?>
						                                                    </ul>
						                                                    <?php endforeach;endif;?>
				                                                </article>
				                                                <?php
                if ( ! empty( $manual_media ) ):
                ?>
				                                                    <div class="manual-content-media">
				                                                        <div class="cc-iframe-responsive">
				                                                            <iframe src="<?php echo esc_url( $manual_media ); ?>" title="DropBox Video Tutorial" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin"></iframe>
				                                                        </div>
				                                                    </div>
				                                                <?php endif;?>
			                                            </div>
			                                        </div>
			                                <?php endforeach;
    endif;?>
	                            </div>
	                        </div>
	                        <!-- user manual End -->
	                        <div class="faq-support-area">
	                            <?php
    if ( ! empty( $is_need_help ) ):
        $section_title = isset( $is_need_help['title'] ) ?  $is_need_help['title'] : '';
        $section_subtitle = isset($is_need_help['subtitle']) ? $is_need_help['subtitle'] : '';
        ?>
		                                <div class="section-title">
		                                    <h2><?php echo esc_html( $section_title ); ?></h2>
		                                    <h5>
		                                        <?php echo esc_html( $section_subtitle ); ?>
		                                    </h5>
		                                </div>
		                            <?php endif;?>
	                            <div class="contract-card-wrapper flex-center">
	                                <?php
    $support_content = isset($is_need_help['support_content']) ? $is_need_help['support_content'] : null;
    if ( ! empty( $support_content ) && is_array( $support_content ) ):
        foreach ( $support_content as $support_area ):
            $support_image = isset( $support_area['image'] ) ?  $support_area['image'] : '';
            $support_title = isset($support_area['title']) ? $support_area['title'] : '';
            $support_description = isset($support_area['description']) ? $support_area['description'] : '';
            $btn_label = isset($support_area['label']) ? $support_area['label'] : '';
            $btn_link = isset($support_area['link']) ? $support_area['link'] : '';
            ?>
			                                        <div class="contract-card d-flex flex-col align-center">
			                                            <div class="contract-image">
			                                                <img src="<?php echo esc_url( INDBOX_ASSETS . "/admin/getting-started/images/{$support_image}" ); ?>" alt="<?php esc_attr( $support_title );?>" />
			                                            </div>
			                                            <div class="contract-meta text-center">
			                                                <h5><?php echo esc_html( $support_title ); ?><h5>
			                                                        <h6>
			                                                            <?php echo esc_attr( $support_description ); ?>
			                                                        </h6>
			                                            </div>
			                                            <a href="<?php echo esc_url( $btn_link ); ?>" class="f-btn"><?php echo esc_html( $btn_label ); ?></a>
			                                        </div>
			                                <?php endforeach;
    endif;?>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </section>
	        <?php endif;?>

        <!-- Dropbox GT Page Tab Area End -->
<?php endforeach;
endif;?>