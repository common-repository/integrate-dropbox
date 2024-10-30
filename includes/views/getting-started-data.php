<?php

$tab_menu_options = [
    [
        'logo' => 'introduction.svg',
        'tab_name' => __('Introduction', 'integrating-dropbox'),
    ],
    [
        'logo' => 'changelog.svg',
        'tab_name' => __('Changelog', 'integrating-dropbox'),
    ],
    [
        'logo' => 'basic_uses.svg',
        'tab_name' => __('Basic Uses', 'integrating-dropbox'),
    ],
    [
        'logo' => 'deta_privacy.svg',
        'tab_name' => __('Data Privacy', 'integrating-dropbox'),
    ],
    [
        'logo' => 'help.svg',
        'tab_name' => __('Help', 'integrating-dropbox'),
    ],

];

$feature_cards = [
    [
        'image' => 'file-browser.png',
        'meta' => __('File Browser', 'integrating-dropbox'),
        'description' => __("Explore Dropbox's wide range of files and folders effortlessly using our user-friendly File Browser feature. With just a few clicks, locate and access specific documents, images, and media files.", 'integrating-dropbox'),
    ],
    [
        'image' => 'file-uploader.png',
        'meta' => __('File Uploader', 'integrating-dropbox'),
        'description' => __("Simplify uploading files to Dropbox folders with our File Uploader tool. Whether it's documents, photos, or videos, easily transfer your files to designated locations, ensuring smooth data management without any technical hassle.", 'integrating-dropbox'),
    ],
    [
        'image' => 'media_player.png',
        'meta' => __('Media Player', 'integrating-dropbox'),
        'description' => __("Experience top-notch multimedia with our advanced Media Player. Dive into high-quality audio and video playback straight from your Dropbox account. Enjoy seamless browsing and immersive viewing and listening.", 'integrating-dropbox'),
    ],
    [
        'image' => 'search_box.png',
        'meta' => __('Search Box', 'integrating-dropbox'),
        'description' => __("Speed up your file search within Dropbox using our advanced Search Box functionality. Simply type in keywords or file names to quickly locate the desired content, saving time and effort while enhancing workflow efficiency.", 'integrating-dropbox'),
    ],
    [
        'image' => 'slider_carousel.png',
        'meta' => __('Slider Carousel', 'integrating-dropbox'),
        'description' => __("Easily engage with your media content using our Slider Carousel feature. Showcase your images and videos in a visually appealing slideshow format, allowing for simple navigation and exploration of your Dropbox media library.", 'integrating-dropbox'),
    ],
    [
        'image' => 'gallery.png',
        'meta' => __('Gallery', 'integrating-dropbox'),
        'description' => __("View your Dropbox content easily with our Gallery view. See images and videos in a grid layout with previews, making it simple to browse and select files, enhancing your content discovery and enjoyment.", 'integrating-dropbox'),
    ],
    [
        'image' => 'embed_doc.png',
        'meta' => __('Embed Documents', 'integrating-dropbox'),
        'description' => __("Effortlessly incorporate Dropbox documents into your platform using our Embed Documents tool. Enhance collaboration and sharing by giving direct access to important files, making document management easier for everyone involved.", 'integrating-dropbox'),
    ],
    [
        'image' => 'download_link.png',
        'meta' => __('Download Link', 'integrating-dropbox'),
        'description' => __("Share Dropbox files effortlessly with our Download Links feature. Generate easily accessible links for downloading files, facilitating smooth file sharing and distribution across platforms.", 'integrating-dropbox'),
    ],
    [
        'image' => 'view_link.png',
        'meta' => __('View Link', 'integrating-dropbox'),
        'description' => __("Easily access Dropbox files with our View Links feature. View files directly, no need to download or install anything extra. Enjoy hassle-free access to content for happier users.", 'integrating-dropbox'),
    ],
];

$explore_features = [
    [
        'image' => 'effortless-int.svg',
        'title' => __('Effortless Integration', 'integrating-dropbox'),
        'description' => __("Integrates Dropbox functionality into the WordPress dashboard, eliminating the need to switch between platforms.", 'integrating-dropbox'),
    ],
    [
        'image' => 'easy-use.svg',
        'title' => __('Easy TO Use Features', 'integrating-dropbox'),
        'description' => __("The Integrate Dropbox plugin features a user-friendly interface designed for simplicity and ease of navigation.", 'integrating-dropbox'),
    ],
    [
        'image' => 'flexible.svg',
        'title' => __('Flexible Customization', 'integrating-dropbox'),
        'description' => __("Tailor the integration to suit your specific requirements with customizable settings and options, ensuring it aligns.", 'integrating-dropbox'),
    ],
];

$changelog = [
    'section_title' => __('Improved performance & stability.', 'integrating-dropbox'),
    'subtitle' => __('New features, smoother navigation, enhanced security, faster loading times, refined visuals, optimized resource usage, improved accessibility, bug fixes', 'integrating-dropbox'),
    'content' => [
        [
            'title' => __('24/10/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.10', 'integrating-dropbox'),
            'version_type' => __('Recent Update', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added option to select a folder for the gallery with autoSync functionality.',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Resolved autoSync issues with files and folders.',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Improved UI and enhanced performance optimization.',
                    ],
                ],
            ]

        ],
        [
            'title' => __('16/10/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.9', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added the ability to update the redirect URL for login authentication.',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Addressed various styling issues for a more polished and consistent look.',
                        'Fixed Tutor LMS video selection issue, ensuring seamless video integration.',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Updated redirect URL for login authentication to enhance security and user experience.',
                        'Minor optimizations to improve overall plugin stability.',
                    ],
                ],
            ]

        ],
        [
            'title' => __('16/10/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.8', 'integrating-dropbox'),
            'version_type' => __('Recent Update', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added support for Tutor LMS integration.',
                        'Added the ability to upload files and folders using the Tutor LMS and MasterStudy LMS Files Selector module.',
                        'Dark Mode feature implemented',
                    ]
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Eliminated unnecessary API calls'
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Enhanced the source page file selector.',
                        'Revamped the Module Preview UI with updated download and view links'
                    ],
                ]
            ],
        ],
        [
            'title' => __('30/09/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.7', 'integrating-dropbox'),
            'version_type' => __('Big Update', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added compatibility for WooCommerce downloadable products',
                        'Integrated support for MasterStudy LMS',
                        'Added enable/disable option for auto-save on the settings page',
                        'Implemented file browser sorting feature for better organization'
                    ]
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Resolved file preview issues on Apache servers'
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Enhanced code efficiency and optimized overall performance'
                    ],
                ]
            ],
        ],
        [
            'title' => __('21/09/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.6', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Auto Sync functionality added, with both custom and built-in time options.',
                        'Recent items feature added, allowing users to quickly access recently used folders and files.',
                        'Center mode feature added to the slider carousel module.',
                        'Grid and list view options added to the file browser module.',
                        'Implement Nested Folder'
                    ]
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Resolved styling issues in the media player module.'
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimized styling code and icons.',
                        'Improved overall code efficiency.'
                    ],
                ]
            ],
        ],
        [
            'title' => __('09/09/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.5', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => ['Added search Module on selected folders']
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Files rerendering while synchronizing'
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Compatible with PHP 7.4.0 or higher',
                        'Update icons and styling'
                    ],
                ]
            ],
        ],
        [
            'title' => __('06/09/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.4', 'integrating-dropbox'),
            'version_type' => __('Big Update', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        '* Big Update * Added Upload Files & Folder Integration',
                        '* Big Update * Create Folder & Upload files and Select folder',
                        '* Big Update *  Added Search functionality for files and folders',
                        'Rename feature in file browser',
                        'Added Download files feature in file browser'
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Elementor rendering issue'
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimize style & plugin Performance'
                    ],
                ]
            ],
        ],
        [
            'title' => __('26/08/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.3', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        '* Big Update * Added Media Library Integration',
                        'Select Specific Folder from media integration',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix File loading issue',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimize style issue on Elementor Editor',
                    ],
                ]
            ],
        ],
        [
            'title' => __('09/08/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.2', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added Media Player Module',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Elementor Editor Issue',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimize style issue on Elementor Editor',
                    ],
                ]
            ],
        ],
        [
            'title' => __('06/08/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.1', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Module builder preview rendering issue',
                        'Fix default view permission issue',
                        'Gutenberg shortcode modules preview issues',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimize style issue on Elementor Editor',
                    ],
                ]
            ],
        ],
        [
            'title' => __('05/08/2024', 'integrating-dropbox'),
            'version_no' => __('1.1.0', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added Elementor widgets integration',
                        'Added Shortcode module in page builder',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Give option to add User\'s Dropbox App',
                        'Optimize file loading time with lazy load',
                        'Update Modules Settings',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Styling issues with Astra, Hello Elementor etc theme',
                    ],
                ],
            ],
        ],
        [
            'title' => __('06/07/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.5', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added Slider Carousel in shortcode module',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimized and cleaned code',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Resolved JS conditional issue',
                    ],
                ],
            ],
        ],
        [
            'title' => __('04/07/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.4', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Added Gutenberg File Browser Module',
                        'Added Gutenberg Embed Documents Module',
                        'Added Gutenberg Download Links Module',
                        'Added Gutenberg View Links Module',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Optimize Database',
                        'Update Gallery Module Settings',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Optimize Database',
                        'Update Gallery Module Settings',
                    ],
                ],
            ],
        ],
        [
            'title' => __('19/06/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.3', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "Resolve PHP error",
                    'logs' => [
                        'Resolve PHP Error',
                    ],
                ],
            ],
        ],
        [
            'title' => __('08/06/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.2', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Gallery module error',
                    ],
                ],
            ],
        ],
        [
            'title' => __('06/06/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.1', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [
                [
                    'title' => "What's New",
                    'logs' => [
                        'Integrate Gutenberg Gallery module',
                        'Added Preloader and Color settings',
                        'Added View links module in shortcode',
                        'Shortcode location in shortcode list page.',
                    ],
                ],
                [
                    'title' => "Updated Features",
                    'logs' => [
                        'Mega Update On UI style',
                        'Updated Shortcode Builder with Gallery settings',
                        'Optimize Performance',
                    ],
                ],
                [
                    'title' => "Fixed Issues",
                    'logs' => [
                        'Fix Gallery module shortcode issue in frontend',
                    ],
                ],
            ],
        ],
        [
            'title' => __('28/04/2024', 'integrating-dropbox'),
            'version_no' => __('1.0.0', 'integrating-dropbox'),
            'description' => sprintf('<h3>%s</h3>', __('Details This Version', 'integrating-dropbox')),
            'log_details' => [

                [
                    'title' => __("Initial Release", 'integrating-dropbox'),
                    'logs' => [
                        __('First Release', 'integrating-dropbox'),
                    ],
                ],
            ],
        ],
    ],
];

$basic_uses = [
    'section_title' => __('Essential Uses and Features', 'integrating-dropbox'),
    'subtitle' => __('This section highlights the fundamental applications and key benefits, providing an overview of how it can be effectively utilized in everyday scenarios.', 'integrating-dropbox'),
    'content' => [
        [
            'title' => __('Installation', 'integrating-dropbox'),
            'description' => __(
                '
            <ul>
                <li>Install the plugin through the WordPress admin or manually upload the integrate-dropbox folder to the /wp-content/plugins/ directory.</li>
                <li>Activate the Integrate Dropbox plugin through the ‘Plugins’ menu in WordPress.</li>
                <li>Go to <a target="_blank" href="https://www.dropbox.com/developers/apps">the Dropbox Developers app console</a></li>
                <li>Press the blue button Create app.</li>
                <li>Choose ‘Dropbox API’ for Step 1.</li>
                <li>Choose ‘Full Dropbox’ for Step 2.</li>
                <li>Set a unique app-name (e.g., [site-prefix]-codeconfig) for Step 3.</li>
                <li>Copy and paste your APP key and App secret into the login screen, then submit credentials.</li>
                <li>Add the auto-regenerated redirect URL from the login screen to App &gt; Settings &gt; OAuth 2 &gt; Redirect URLs, then click on the add button.</li>
                <li>Add the necessary permissions from the Permissions tab.</li>
                <li>Finally, click on the sign-in button to authorize with your Dropbox account.</li>
            </ul>
            <p>Integrate Dropbox is now installed and configured. <a target="_blank" href="https://codeconfig.dev/docs/how-to-connect-my-dropbox-app-with-wordpress/">Documentation</a> | <a target="_blank" href="https://www.youtube.com/watch?v=YivpoNE8ukk">Video</a></p>',
                'integrating-dropbox'
            ),
        ],
        [
            'title' => __('Link a Dropbox account', 'integrating-dropbox'),
            'description' => __(
                '
            <h4>Link Dropbox Account</h4>
            <h6>After activating the plugin, you have to link your Dropbox account to the plugin. You can link multiple Dropbox accounts to the plugin.</h6>
            <h5>Follow the steps below to add a Dropbox account to the plugin :</h5>
            <ul>
                <li>Go to the <strong>Dropbox</strong> or <strong>Dropbox > Settings</strong> page in the WordPress admin dashboard on your website.</li>
                <li>Click on the <strong>Add account</strong> button.</li>
                <li>A new window will open, and you will be redirected to the Dropbox login page to log in with your email.</li>
                <li>Select the email address with which you want to log in.</li>
                <li>Click the <strong>Allow</strong> button to authorize the plugin to access your Dropbox data.</li>
                <li>Wait for the authorization process, and then you are done!</li>
            </ul>',
                'integrating-dropbox'
            ),
        ],
        [
            'title' => __('Shortcode Builder Module', 'integrating-dropbox'),
            'description' => __('
            <h3>Shortcode Module Builder</h3>
            <h6>You can create any number of shortcode modules and use them in your post/page using the <code>[integrate_dropbox id="1"]</code> shortcode.</h6>
            <h5>There are several module types to create a shortcode. These include :</h5>
            <ul>
                <li><strong>Media Library</strong> - Integrate Media Library and access your Dropbox in WordPress Features, Add Media etc scope</li>
                <li><strong>Search Files & Folders</strong> - Search Files and Folders from search field</li>
                <li><strong>File Browser</strong> - Browse your Dropbox files.</li>
                <li><strong>Build Shortcode</strong> - Create Shortcode with different settings to add your page/post.</li>
                <li><strong>Photo Gallery</strong> - Lightbox grid photo gallery module.</li>
                <li><strong>Slider Carousel</strong> - User Slider Carousel module to create dynamic options.</li>
                <li><strong>Preloader</strong> - Use Preloader in your website</li>  
            </ul>
            ', 'integrating-dropbox'),
        ],
    ],
];

$data_privacy = [
    'section_title' => __('Data Privacy', 'integrating-dropbox'),
    'subtitle' => __('We prioritize your data privacy above all else, ensuring your information remains secure and inaccessible to unauthorized parties at all times.', 'integrating-dropbox'),
    'content' => [
        [
            'title' => __('Plugin Authorization Flow (OAuth) for Dropbox', 'integrating-dropbox'),
            'description' => __('
            <h4>Overview</h4>
            <p>This document outlines the OAuth-based authorization flow for plugins, providing a secure and efficient method for users to grant access to their Dropbox resources without sharing their credentials.</p>

            <h4>Steps in the Authorization Flow</h4>
            <p>For technical reasons, the short-lived authorization code generated by the OAuth flow is sent by your internet browser to our server as part of the authentication (OAuth) flow to provide the Application access to the Dropbox API.</p>

            <p>The Application obtains the following information when you use the built-in app for authentication and link the Application with your Dropbox account:</p>

            <ul>
                <li>Your WordPress Website Address</li>
                <li>A Short-Lived Authorization Code Generated by the OAuth Flow</li>
            </ul>

            <p>This information is obtained and used after you decide to grant the Application the requested access via the Dropbox OAuth consent screen. After giving consent, you will be redirected to the server of <a href="https://codeconfig.dev/integrate-dropbox-oauth.php">CodeConfig</a>, which will redirect you back to your own site where the authorization process is finalized. This redirect via the server of codeconfig.dev is required for an easy plugin setup where you don’t need to create your own Dropbox App, which also allows you to set your own Authorized Redirect URI.</p>

            <h4>On Your Own Server</h4>
            <p>On your own server, the short-lived authorization code will be exchanged for the actual access token and refresh token, which are stored, encrypted, on your server. The authorization code can only be used once and will immediately become inactive after it has been exchanged for the access token or within minutes if it is not used.</p>

            <h4>Security Considerations</h4>
            <ul>
                <li><strong>Confidential Client Information:</strong> Keep the client secret secure and do not expose it in the frontend or to unauthorized individuals.</li>
                <li><strong>Token Storage:</strong> Store tokens securely, using encryption and secure storage mechanisms to prevent unauthorized access.</li>
                <li><strong>Token Expiry:</strong> Handle token expiry appropriately by using refresh tokens to obtain new access tokens without user intervention.</li>
                <li><strong>Scopes:</strong> Request only the scopes necessary for the plugin to function, adhering to the principle of least privilege.</li>
            </ul>

            <h4>Important Notes</h4>
            <p>When you use the Application, all other communications are strictly between your server and the cloud storage service servers. The communication is encrypted, and it will not go through our servers. We do not collect and do not have access to your files.</p>

            <h4>Data Privacy</h4>
            <p>We prioritize your data privacy above all else, ensuring your information remains secure and inaccessible to unauthorized parties at all times.</p>

            <h4>Conclusion</h4>
            <p>Following this OAuth authorization flow ensures a secure and efficient way for users to authorize plugins to access their Dropbox resources without compromising their credentials. Proper implementation and adherence to security best practices are essential to maintain the integrity and confidentiality of user data.</p>

            <p>For more details, please refer to the <a href="https://codeconfig.dev/privacy-policy">CodeConfig Privacy Policy</a>.</p>

            ', 'integrating-dropbox'),
        ],
        [
            'title' => __('OAuth Permissions for Dropbox', 'integrating-dropbox'),
            'description' => __('
            <h3>OAuth Permissions for Dropbox</h3>
            <p>OAuth (Open Authorization) is a widely used authorization protocol that allows third-party applications to securely access resources on behalf of a user without exposing their credentials. In the context of Dropbox, OAuth permissions dictate the level of access granted to an application to interact with a user`s Dropbox account.</p>

            <h4>Basic Access</h4>
            <ul>
                <li>Basic access permissions typically include read-only access to basic account information, such as user profile details and file metadata.</li>
                <li>Applications with basic access permissions can view files and folders stored in the user`s Dropbox account but cannot make any changes.</li>
            </ul>

            <h4>Full Access</h4>
            <ul>
                <li>Full access permissions grant applications complete control over a user`s Dropbox account, allowing them to create, modify, and delete files and folders.</li>
                <li>Applications with full access permissions can perform actions such as uploading new files, modifying existing files, and managing folder structures.</li>
            </ul>

            <h4>App Folder Access</h4>
            <ul>
                <li>App folder access restricts an application`s access to a specific folder within the user`s Dropbox account, typically named after the application.</li>
                <li>Applications with app folder access can only interact with files and folders within this designated folder, ensuring that they do not have access to the user`s entire Dropbox storage.</li>
            </ul>

            <h4>Limited Access</h4>
            <ul>
                <li>Limited access permissions allow developers to define custom scopes and restrictions based on the specific functionality required by their application.</li>
                <li>This approach enables fine-grained control over the types of actions an application can perform within a user`s Dropbox account, tailored to the application`s requirements.</li>
            </ul>

            <h4>Revoking Permissions</h4>
            <ul>
                <li>Users have the ability to review and revoke the permissions granted to third-party applications at any time.</li>
                <li>This ensures that users maintain control over their data and can revoke access to their Dropbox account if they no longer trust or use a particular application.</li>
            </ul>

            <h4>Conclusion</h4>
            <p>OAuth permissions play a crucial role in ensuring the security and privacy of user data when integrating third-party applications with Dropbox. By carefully managing permissions and only granting the necessary level of access, users can enjoy the convenience of using external services while maintaining control over their Dropbox account`s security.</p>

            <p>For more details, please refer to the <a href="https://codeconfig.dev/privacy-policy">CodeConfig Privacy Policy</a>.</p>

            ', 'integrating-dropbox'),
        ],
    ],
];

$help = [
    'section_title' => __('Frequently Asked Questions', 'integrating-dropbox'),
    'subtitle' => __('Your Go-To Guide for Common Inquiries: Find answers to the most frequently asked questions quickly and easily to make the most of your experience.', 'integrating-dropbox'),
    'content' => [
        [
            'title' => __('What is the Integrate Dropbox Plugin?', 'integrating-dropbox'),
            'description' => __('The Dropbox integration plugin for WordPress allows you to connect your WordPress site with Dropbox, enhancing functionality and providing seamless file management', 'integrating-dropbox'),
        ],
        [
            'title' => __('Can I share Dropbox files on WordPress posts or pages easily?', 'integrating-dropbox'),
            'description' => __('Absolutely! The Dropbox plugin allows you to embed Dropbox files or folders directly into your WordPress posts or pages with just a few clicks. Whether you want to share documents, images, or any other type of file, this feature makes it simple to enrich your content and engage your audience with relevant resources.', 'integrating-dropbox'),
        ],
        [
            'title' => __('Can I back up my WordPress site effortlessly with the Integrate Dropbox plugin?', 'integrating-dropbox'),
            'description' => __('Yes, the plugin allows seamless backups to Dropbox.', 'integrating-dropbox'),
        ],
        [
            'title' => __('Can I selectively upload files from Dropbox to my WordPress media library?', 'integrating-dropbox'),
            'description' => __('Yes, you can choose specific files or folders in Dropbox and upload them directly to your WordPress media library. This feature streamlines media management and ensures only relevant files are added to your website.', 'integrating-dropbox'),
        ],

    ],

    'need_help' => [
        'title' => __('Need Help', 'integrating-dropbox'),
        'subtitle' => __('Read our knowledge base documentation or you can contact us directly.', 'integrating-dropbox'),
        'support_content' => [
            [
                'image' => __('support.png', 'integrating-dropbox'),
                'title' => __('Support', 'integrating-dropbox'),
                'description' => __('Visit Our Support', 'integrating-dropbox'),
                'label' => __('Support', 'integrating-dropbox'),
                'link' => '/wp-admin/admin.php?page=integrate-dropbox-contact',
            ],
            [
                'image' => 'contact-us.png',
                'title' => __('Support', 'integrating-dropbox'),
                'description' => __('Contact Us for any assistant', 'integrating-dropbox'),
                'label' => __('Contact', 'integrating-dropbox'),
                'link' => '/wp-admin/admin.php?page=integrate-dropbox-contact',
            ],
        ],
    ],

];

$tab_feature_actions = [
    'Changelog' => $changelog,

    'Basic Uses' => $basic_uses,

    'Data Privacy' => $data_privacy,

    'Help' => $help,
];
