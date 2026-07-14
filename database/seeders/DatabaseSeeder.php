<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Category;
use App\Models\Article;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Permissions
        $permissions = [
            'manage-users' => 'Manage dashboard users and roles',
            'edit-site-sections' => 'Edit website pages and sections',
            'manage-articles' => 'Create, edit, and publish articles/news',
            'manage-events' => 'Create, edit, and manage events',
            'manage-media' => 'Upload, view, and delete media files',
            'edit-settings' => 'Edit general organization website settings',
        ];

        $permissionModels = [];
        foreach ($permissions as $slug => $name) {
            $permissionModels[$slug] = Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        }

        // 2. Roles
        $adminRole = Role::updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator']
        );

        $editorRole = Role::updateOrCreate(
            ['slug' => 'editor'],
            ['name' => 'Editor']
        );

        // Associate Permissions to Roles
        // Admin gets all permissions
        $adminRole->permissions()->sync(array_map(fn($p) => $p->id, array_values($permissionModels)));
        
        // Editor gets articles, events, media
        $editorRole->permissions()->sync([
            $permissionModels['manage-articles']->id,
            $permissionModels['manage-events']->id,
            $permissionModels['manage-media']->id,
        ]);

        // 3. Users
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@gyv.org'],
            [
                'name' => 'GYV Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        $adminUser->roles()->sync([$adminRole->id]);

        $editorUser = User::updateOrCreate(
            ['email' => 'editor@gyv.org'],
            [
                'name' => 'GYV Content Editor',
                'password' => Hash::make('editor123'),
                'email_verified_at' => now(),
            ]
        );
        $editorUser->roles()->sync([$editorRole->id]);

        // 4. Pages
        $homePage = Page::updateOrCreate(
            ['slug' => 'home'],
            [
                'title' => 'Home Page',
                'meta_title' => 'Global Youth Voices - Make Big Tobaccos Pay',
                'meta_description' => 'Uniting Youth Voices and making Big Tobaccos Pay. Global Youth Voices Call on COP10 to ban harmful products.',
            ]
        );

        // 5. Page Sections
        // Home Hero
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'hero'],
            [
                'title' => 'Hero Section',
                'content' => [
                    'title' => 'Uniting',
                    'highlight' => 'Youth',
                    'title_suffix' => 'Voices.',
                    'subtitle' => 'Making Big Tobaccos Pay.',
                    'primary_cta_text' => 'Join the Movement',
                    'primary_cta_link' => '/campaigns',
                    'secondary_cta_text' => 'Read the Declaration',
                    'secondary_cta_link' => '/declarations',
                    'bg_image' => '/homepage/hero/bg.jpg',
                    'bubbles' => [
                        ['text' => 'Lorem ipsum dolor sit amet consectetur.', 'author' => 'Random Name'],
                        ['text' => 'Lorem ipsum dolor sit amet consectetur.', 'author' => 'Random Name'],
                        ['text' => 'Lorem ipsum dolor sit amet consectetur.', 'author' => 'Random Name'],
                        ['text' => 'Lorem ipsum dolor sit amet consectetur.', 'author' => 'Random Name'],
                    ]
                ]
            ]
        );

        // Home Youth Statement
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'youth'],
            [
                'title' => 'Global Youth Statement Section',
                'content' => [
                    'heading' => 'Global Youth',
                    'highlight' => 'Statement',
                    'subtitle' => 'Global Youth Voices Call on COP10: Ban Harmful Products, Demand Accountability',
                    'description' => 'Over 30 youth organizations worldwide call on COP10 to take decisive action against tobacco industry tactics and hold the industry financially accountable for the harms it inflicts.',
                    'cta_text' => 'View the full Statement',
                    'cta_link' => '/declarations',
                    'statements' => [
                        'A ban on novel recreational addictive products',
                        'Stronger measures on liability and financial accountability',
                        'Stricter regulation of tobacco depiction in entertainment and digital media',
                        'Robust advancement of global tobacco control policies'
                    ]
                ]
            ]
        );

        // Home Milestones
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'milestones'],
            [
                'title' => 'Key Milestones Timeline',
                'content' => [
                    'heading' => 'Key Milestones',
                    'description' => 'A visual timeline tracking the global progress of Global Youth Voices and tobacco control landmarks.',
                    'milestones' => [
                        ['year' => '2012', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768'],
                        ['year' => '2013', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a3eb1d19111b8d8595f583afcee61f7093186ab5?width=768'],
                        ['year' => '2014', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768'],
                        ['year' => '2015', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a3eb1d19111b8d8595f583afcee61f7093186ab5?width=768'],
                        ['year' => '2016', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768'],
                        ['year' => '2017', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a3eb1d19111b8d8595f583afcee61f7093186ab5?width=768'],
                        ['year' => '2018', 'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.', 'image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768']
                    ]
                ]
            ]
        );

        // Home CTA
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'cta'],
            [
                'title' => 'Take Action CTA Section',
                'content' => [
                    'tag' => 'Take Action',
                    'title_prefix' => 'Lorem ipsum dolor sit',
                    'highlight' => 'amet,',
                    'subtitle_part' => 'consectetur',
                    'title_middle' => 'adipiscing elit. Ut et massa mi.',
                    'subtitle_part2' => 'Aliquam',
                    'title_suffix' => 'in hendrerit',
                    'highlight2' => 'urna.',
                    'primary_btn_text' => 'Join the Movement',
                    'primary_btn_link' => '/campaigns',
                    'secondary_btn_text' => 'Read the Declaration',
                    'secondary_btn_link' => '/declarations'
                ]
            ]
        );

        // ══════════════════════════════════════════
        // CAMPAIGNS PAGE — all 4 sections
        // ══════════════════════════════════════════
        $campaignsPage = Page::updateOrCreate(
            ['slug' => 'campaigns'],
            [
                'title' => 'Campaigns',
                'meta_title' => 'Campaigns | Global Youth Voices',
                'meta_description' => 'Explore and support youth-led campaigns pushing for tobacco control, smoke-free campuses, and safer communities.',
            ]
        );

        // Campaigns — Hero Section
        PageSection::updateOrCreate(
            ['page_id' => $campaignsPage->id, 'section_key' => 'hero'],
            [
                'title' => 'Campaigns Hero',
                'content' => [
                    'eyebrow' => 'Campaigns',
                    'heading' => 'Campaigns Advocated by Youth Organizations and Partners',
                    'description' => 'Explore live tobacco control campaigns where youth are demanding smoke-free campuses, stronger flavor bans, and better protections from industry marketing.',
                ]
            ]
        );

        // Campaigns — Signature Campaigns Section
        PageSection::updateOrCreate(
            ['page_id' => $campaignsPage->id, 'section_key' => 'signature_campaigns'],
            [
                'title' => 'Signature Campaigns',
                'content' => [
                    'heading' => 'Signature Campaigns',
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                    'campaigns' => [
                        [
                            'date_label' => 'May 31',
                            'title' => 'World No Tobacco Day',
                            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna.',
                            'supporters_label' => 'Advocated by 3 youth organizations',
                            'signatures_label' => '449 supporters',
                            'image' => '/no-tobacco.jpg',
                            'slug' => 'smoke-free-campuses',
                            'cta_text' => 'Sign the Declaration',
                        ],
                        [
                            'date_label' => 'July 15',
                            'title' => 'Ban Flavored Nicotine Products',
                            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna.',
                            'supporters_label' => 'Advocated by 535 youth organizations',
                            'signatures_label' => '69,713 supporters',
                            'image' => '/no-tobacco.jpg',
                            'slug' => 'ban-flavored-products',
                            'cta_text' => 'Sign the Declaration',
                        ],
                        [
                            'date_label' => 'October 10',
                            'title' => 'Stronger Oversight on Youth-Targeted Ads',
                            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna.',
                            'supporters_label' => 'Advocated by 1,432 youth organizations',
                            'signatures_label' => '59,675 supporters',
                            'image' => '/no-tobacco.jpg',
                            'slug' => 'industry-marketing-oversight',
                            'cta_text' => 'Sign the Declaration',
                        ],
                    ],
                ]
            ]
        );

        // Campaigns — Upcoming Campaigns Section
        PageSection::updateOrCreate(
            ['page_id' => $campaignsPage->id, 'section_key' => 'upcoming_campaigns'],
            [
                'title' => 'Upcoming Campaigns',
                'content' => [
                    'heading' => 'Upcoming Campaigns',
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                    'campaigns' => [
                        [
                            'slug' => 'philippine-vape-ban',
                            'title' => 'Philippine Vape Ban Campaign',
                            'date' => 'DECEMBER 31',
                            'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit uma. Pellentesque sit amet sapien fringilla, mattis ligula consectetur,...',
                            'image' => '/upcoming/philippine-vape-ban.png',
                        ],
                        [
                            'slug' => 'clean-air-initiative',
                            'title' => 'Clean Air Initiative',
                            'date' => 'JANUARY 15',
                            'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit uma. Pellentesque sit amet sapien fringilla, mattis ligula consectetur,...',
                            'image' => '/upcoming/clean-air.png',
                        ],
                    ],
                ]
            ]
        );

        // Campaigns — Past Campaigns Section
        PageSection::updateOrCreate(
            ['page_id' => $campaignsPage->id, 'section_key' => 'past_campaigns'],
            [
                'title' => 'Past Campaigns',
                'content' => [
                    'heading' => 'Past Campaigns',
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                    'campaigns' => [
                        [
                            'id' => 'past-1',
                            'year' => '2026',
                            'title' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                            'date' => 'Month DD-DD, YYYY',
                            'location' => 'Location, City',
                            'image' => '/past-no-smoking.png',
                            'link' => '#',
                        ],
                        [
                            'id' => 'past-2',
                            'year' => '2026',
                            'title' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                            'date' => 'Month DD-DD, YYYY',
                            'location' => 'Location, City',
                            'image' => '/past-no-smoking.png',
                            'link' => '#',
                        ],
                    ],
                ]
            ]
        );

        // ══════════════════════════════════════════
        // DECLARATIONS PAGE — all 4 sections
        // ══════════════════════════════════════════
        $declarationsPage = Page::updateOrCreate(
            ['slug' => 'declarations'],
            [
                'title' => 'Declarations',
                'meta_title' => 'Declarations | Global Youth Voices',
                'meta_description' => 'Read the official GYV youth declarations calling on governments and COP10 delegates to take decisive action on tobacco control.',
            ]
        );

        // Declarations — Hero Section
        PageSection::updateOrCreate(
            ['page_id' => $declarationsPage->id, 'section_key' => 'hero'],
            [
                'title' => 'Declarations Hero',
                'content' => [
                    'eyebrow' => 'Declarations',
                    'heading_line1' => 'All current campaign',
                    'heading_highlight' => 'declarations',
                    'description' => 'Explore the current policy asks and declaration statements that guide Global Youth Voices advocacy.',
                    'illustration' => '/declarations.png',
                ]
            ]
        );

        // Declarations — Declaration Cards (First Declaration group)
        PageSection::updateOrCreate(
            ['page_id' => $declarationsPage->id, 'section_key' => 'first_declaration'],
            [
                'title' => 'First Declaration Block',
                'content' => [
                    'declarations' => [
                        [
                            'slug' => 'ban-novel-recreational-products',
                            'title' => 'Ban on novel recreational addictive products',
                            'summary' => 'Call for stricter controls on emerging nicotine and tobacco products that target young people.',
                            'details' => [
                                'Prevent new nicotine products from being marketed as lifestyle or youth-focused experiences.',
                                'Close regulatory gaps around flavored and disposable vape devices.',
                            ],
                        ],
                        [
                            'slug' => 'financial-accountability-and-liability',
                            'title' => 'Stronger liability and financial accountability',
                            'summary' => 'Demand legal accountability for manufacturers and distributors that profit from harmful products.',
                            'details' => [
                                'Hold companies responsible for health costs related to youth addiction.',
                                'Require transparent reporting on marketing spend targeting young audiences.',
                            ],
                        ],
                    ],
                ]
            ]
        );

        // Declarations — Second Declaration group
        PageSection::updateOrCreate(
            ['page_id' => $declarationsPage->id, 'section_key' => 'second_declaration'],
            [
                'title' => 'Second Declaration Block',
                'content' => [
                    'declarations' => [
                        [
                            'slug' => 'tobacco-depiction-regulation',
                            'title' => 'Regulate tobacco depiction in entertainment and digital media',
                            'summary' => 'Ask for limits on how tobacco and nicotine products are portrayed in media popular with youth.',
                            'details' => [
                                'Reduce glamorization of smoking and vaping in film, TV, and online content.',
                                'Ensure age-appropriate warnings accompany product depiction in digital campaigns.',
                            ],
                        ],
                        [
                            'slug' => 'advance-global-tobacco-control',
                            'title' => 'Advance global tobacco control policies',
                            'summary' => 'Push for stronger international commitments and alignment with youth health priorities.',
                            'details' => [
                                'Support adoption of stricter tobacco control measures in regional health agreements.',
                                'Raise youth voices in global policy forums and accountability mechanisms.',
                            ],
                        ],
                    ],
                ]
            ]
        );

        // Declarations — Take Action CTA
        PageSection::updateOrCreate(
            ['page_id' => $declarationsPage->id, 'section_key' => 'take_action_cta'],
            [
                'title' => 'Declarations Take Action CTA',
                'content' => [
                    'heading' => 'Ready to take action?',
                    'description' => 'Join thousands of youth advocates and sign the declaration today.',
                    'primary_btn_text' => 'Sign the Declaration',
                    'primary_btn_link' => '/campaigns',
                    'secondary_btn_text' => 'Learn More',
                    'secondary_btn_link' => '/about-us',
                ]
            ]
        );

        // ══════════════════════════════════════════
        // ABOUT US PAGE — all 9 sections
        // ══════════════════════════════════════════
        $aboutPage = Page::updateOrCreate(
            ['slug' => 'about-us'],
            [
                'title' => 'About Us',
                'meta_title' => 'About Global Youth Voices | Our Mission & Vision',
                'meta_description' => 'Learn about Global Youth Voices — who we are, our mission to protect youth from tobacco industry harms, and the team driving change.',
            ]
        );

        // About — Hero
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'hero'],
            [
                'title' => 'About Hero',
                'content' => [
                    'eyebrow' => 'About Us',
                    'heading_prefix' => 'What is the Global',
                    'heading_highlight' => 'Youth',
                    'heading_suffix' => 'Voices?',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.',
                    'illustration' => '/about-us.png',
                ]
            ]
        );

        // About — Who We Are
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'who_we_are'],
            [
                'title' => 'Who We Are',
                'content' => [
                    'heading' => 'Who We Are',
                    'statement' => 'Global Youth Voices is a youth movement convened by the Global Center for Good Governance in Tobacco Control that brings together youth organizations, coalitions, and young advocates working to protect public health from tobacco industry interference.',
                    'description_paragraphs' => [
                        'Through campaigns, youth statements, policy advocacy, and regional engagement, GYV amplifies young people\'s calls for stronger tobacco control, industry accountability, and protection from nicotine addiction and industry-driven harms.',
                        'GYV provides a platform for youth voices to be heard not only in awareness campaigns, but also in policy spaces where decisions on tobacco control, public health, and accountability are shaped.',
                    ],
                    'banner_image' => '/who-we-are.png',
                ]
            ]
        );

        // About — Why We Exist
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'why_we_exist'],
            [
                'title' => 'Why We Exist',
                'content' => [
                    'eyebrow' => 'LOREM IPSUM',
                    'heading' => 'Why We Exist',
                    'left_image' => '/upcoming/clean-air.png',
                    'decorative_image' => '/why-we-exist.png',
                    'body_paragraphs' => [
                        'Young people are being targeted by tobacco and nicotine industries through flavors, digital marketing, influencers, lifestyle branding, misleading harm reduction narratives, and weak regulation. At the same time, youth voices are often used symbolically but not always meaningfully included in policy advocacy.',
                        'GYV exists to make youth participation more organized, evidence-based, independent, and connected to global tobacco control processes.',
                    ],
                ]
            ]
        );

        // About — What We Stand For
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'what_we_stand_for'],
            [
                'title' => 'What We Stand For',
                'content' => [
                    'eyebrow' => 'LOREM IPSUM',
                    'heading' => 'What We Stand For',
                    'cta_text' => 'View entire declaration',
                    'cta_link' => '/declarations',
                    'cards' => [
                        ['number' => '01', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                        ['number' => '02', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi.'],
                        ['number' => '03', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut et massa mi. Aliquam in hendrerit urna.'],
                        ['number' => '04', 'text' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
                    ],
                ]
            ]
        );

        // About — How GYV Works
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'how_gyv_works'],
            [
                'title' => 'How GYV Works',
                'content' => [
                    'heading' => 'How GYV Works',
                    'description' => 'GYV works by connecting youth organizations, advocates, and regional spokespersons around shared advocacy priorities.',
                    'illustration' => '/how-gyv-works.png',
                    'work_items_heading' => 'Our work includes',
                    'work_items' => [
                        ['title' => 'CAMPAIGNS', 'description' => 'We develop and support global and regional campaigns around key advocacy moments such as World No Tobacco Day, International Youth Day, and WHO FCTC-related processes.', 'border_color' => '#0E85B1'],
                        ['title' => 'YOUTH STATEMENTS AND LETTERS', 'description' => 'We help consolidate youth perspectives into statements, calls to action, and campaign messages that can be used in global and regional advocacy.', 'border_color' => '#f06474'],
                        ['title' => 'REGIONAL VOICES', 'description' => 'GYV works with youth spokespersons across WHO regions to help reflect diverse experiences, priorities, and realities from different parts of the world.', 'border_color' => '#fecf02'],
                        ['title' => 'YOUTH MOBILIZATION', 'description' => 'Participating organizations help localize, translate, adapt, and amplify GYV messages in their own countries and communities.', 'border_color' => '#7c3aed'],
                        ['title' => 'POLICY ADVOCACY', 'description' => 'Participating organizations help localize, translate, adapt, and amplify GYV messages in their own countries and communities.', 'border_color' => '#22c55e'],
                    ],
                ]
            ]
        );

        // About — Spokespersons / Leadership
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'spokespersons'],
            [
                'title' => 'Coordination & Regional Spokespersons',
                'content' => [
                    'heading' => 'Coordination and Regional Spokespersons',
                    'description' => 'Meet the regional coordinators and spokespersons who represent youth voices at the highest levels of global health policy.',
                    'spokespersons' => [
                        ['name' => 'Spokesperson Name', 'region' => 'Global Coordinator', 'image' => '/spokesperson-placeholder.png'],
                        ['name' => 'Spokesperson Name', 'region' => 'Africa Region', 'image' => '/spokesperson-placeholder.png'],
                        ['name' => 'Spokesperson Name', 'region' => 'Asia Pacific', 'image' => '/spokesperson-placeholder.png'],
                        ['name' => 'Spokesperson Name', 'region' => 'Europe', 'image' => '/spokesperson-placeholder.png'],
                    ],
                ]
            ]
        );

        // About — Global Network / Stats & Partners
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'global_network'],
            [
                'title' => 'Our Global Network',
                'content' => [
                    'heading' => 'Our Global Network',
                    'stats' => [
                        ['value' => '200+', 'label' => 'Campaigns Launched'],
                        ['value' => '5M+', 'label' => 'Petitions Signed'],
                        ['value' => '20+', 'label' => 'Events Held'],
                        ['value' => '20K+', 'label' => 'Members Worldwide'],
                    ],
                    'partners' => [
                        ['name' => 'Vital Strategies'],
                        ['name' => 'FCTC Secretariat'],
                        ['name' => 'Campaign for Tobacco-Free Kids'],
                        ['name' => 'World Health Organization'],
                        ['name' => 'Southeast Asia Tobacco Control Alliance'],
                        ['name' => 'Global Center for Good Governance in Tobacco Control'],
                    ],
                ]
            ]
        );

        // About — Our Principles
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'principles'],
            [
                'title' => 'Our Principles',
                'content' => [
                    'heading' => 'Our Principles',
                    'intro' => 'GYV\'s work is guided by the following principles:',
                    'principles' => [
                        ['title' => 'Independence from the tobacco industry', 'description' => 'GYV does not partner with, accept support from, or promote the interests of the tobacco or nicotine industry.'],
                        ['title' => 'Alignment with WHO FCTC Article 5.3', 'description' => 'GYV supports the protection of public health policies from tobacco industry interference.'],
                        ['title' => 'Youth-led and youth-centered advocacy', 'description' => 'GYV believes young people should be meaningfully involved in shaping messages, campaigns, and advocacy priorities that affect their generation.'],
                        ['title' => 'Evidence-based communication', 'description' => 'GYV combines creative youth engagement with credible public health evidence and policy-aligned messaging.'],
                        ['title' => 'Global solidarity', 'description' => 'GYV recognizes that tobacco industry tactics affect young people across countries and regions, and that collective action strengthens advocacy.'],
                        ['title' => 'Accountability', 'description' => 'GYV calls for the tobacco industry to be held accountable for the health, environmental, social, and economic harms it causes.'],
                    ],
                ]
            ]
        );

        // About — Join or Partner With Us
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'join'],
            [
                'title' => 'Join or Partner With Us',
                'content' => [
                    'heading_prefix' => 'Join or',
                    'heading_highlight' => 'Partner',
                    'heading_suffix' => 'With Us!',
                    'body_paragraphs' => [
                        'GYV welcomes youth organizations, student groups, and public health partners who share our commitment to protecting young people from tobacco industry tactics.',
                        'Whether through campaigns, statements, regional engagement, or local advocacy, there are many ways to be part of the movement.',
                    ],
                    'cta_text' => 'Join the movement',
                    'cta_link' => '/contact',
                ]
            ]
        );

        // ══════════════════════════════════════════
        // EVENTS PAGE — all 3 sections
        // ══════════════════════════════════════════
        $eventsPage = Page::updateOrCreate(
            ['slug' => 'events'],
            [
                'title' => 'Events',
                'meta_title' => 'Events & Summits | Global Youth Voices',
                'meta_description' => 'Upcoming and past GYV events, summits, workshops, and panel discussions on tobacco control advocacy.',
            ]
        );

        PageSection::updateOrCreate(
            ['page_id' => $eventsPage->id, 'section_key' => 'hero'],
            [
                'title' => 'Events Hero',
                'content' => [
                    'eyebrow' => 'Events',
                    'heading' => 'Recent Events',
                    'description' => 'Keep track of our workshops, panel talks, summits, and campaign schedules.',
                ]
            ]
        );

        PageSection::updateOrCreate(
            ['page_id' => $eventsPage->id, 'section_key' => 'events_list'],
            [
                'title' => 'Events Listing Section',
                'content' => [
                    'heading' => 'All Events',
                    'description' => 'GYV hosts and participates in events across the globe — from regional workshops to major international summits. Register to attend, volunteer, or present.',
                    'empty_state_text' => 'No upcoming events at this time. Check back soon!',
                ]
            ]
        );

        PageSection::updateOrCreate(
            ['page_id' => $eventsPage->id, 'section_key' => 'cta'],
            [
                'title' => 'Events Page CTA',
                'content' => [
                    'heading' => 'Want to host an event with GYV?',
                    'description' => 'Reach out to our team to collaborate on a summit, workshop, or webinar.',
                    'cta_text' => 'Contact Us',
                    'cta_link' => '/contact',
                ]
            ]
        );

        // ══════════════════════════════════════════
        // RESOURCES PAGE — all 5 sections
        // ══════════════════════════════════════════
        $resourcesPage = Page::updateOrCreate(
            ['slug' => 'resources'],
            [
                'title' => 'Resources',
                'meta_title' => 'Resources & Toolkits | Global Youth Voices',
                'meta_description' => 'Access GYV advocacy toolkits, knowledge publications, official communications, and media assets.',
            ]
        );

        // Resources — Hero
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'hero'],
            [
                'title' => 'Resources Hero',
                'content' => [
                    'eyebrow' => 'Resources',
                    'heading_highlight' => 'Tools',
                    'heading_line2' => 'to inform,',
                    'heading_line3' => 'advocate, and',
                    'heading_line4' => 'mobilize.',
                    'description' => 'Browse our latest briefs, statements, social media assets, and templates.',
                    'bubble_1_title' => 'Access advocacy tools',
                    'bubble_1_body' => 'Resources for your campaign',
                    'bubble_2_title' => 'Mobilize your community',
                    'bubble_2_body' => 'Templates & guides',
                ]
            ]
        );

        // Resources — All Categories
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'categories'],
            [
                'title' => 'Resource Categories Grid',
                'content' => [
                    'heading' => 'All Categories',
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                    'categories' => [
                        ['label' => 'Briefs & Factsheets', 'link' => '/resources/knowledge-publications'],
                        ['label' => 'Letters and Statements', 'link' => '/resources/official-communications'],
                        ['label' => 'Toolkits & Campaign Info', 'link' => '/resources/toolkits'],
                        ['label' => 'Media Articles', 'link' => '/resources/media'],
                        ['label' => 'Events', 'link' => '/events'],
                        ['label' => 'Videos', 'link' => '/resources/media'],
                    ],
                ]
            ]
        );

        // Resources — Latest Resources (Featured Slideshow + 3 cards)
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'latest_resources'],
            [
                'title' => 'Latest Resources Slideshow',
                'content' => [
                    'heading' => 'Latest Resources',
                    'description' => 'Lorem ipsum dolor sit amet consectetur adipiscing.',
                    'featured_resources' => [
                        [
                            'id' => 1,
                            'category' => 'Briefs & Factsheets',
                            'title' => 'Youth Advocacy Toolkit 2026: Modern Campaign Strategies',
                            'description' => 'Explore the comprehensive guide on modern digital campaign tactics, community mobilization, and coalition building for youth-led health advocacy. Learn how to craft compelling messages and engage with policymakers effectively.',
                            'image' => '/resources/resources_featured.png',
                            'link' => '/resources/knowledge-publications',
                        ],
                        [
                            'id' => 2,
                            'category' => 'Letters & Statements',
                            'title' => 'Joint Statement on Smoke-Free Environment Policies',
                            'description' => 'Our official collective statement co-signed by 42 international youth-led organizations, calling on local education departments to expand smoke-free zoning around schools and universities.',
                            'image' => '/resources/youth_highfive.png',
                            'link' => '/resources/official-communications',
                        ],
                        [
                            'id' => 3,
                            'category' => 'Toolkits',
                            'title' => 'Grassroots Organizing: Step-by-Step Workshop Manual',
                            'description' => 'A plug-and-play toolkit containing presentation slides, feedback templates, and workshop agendas designed to help high school and university advocates run local training sessions on tobacco marketing regulations.',
                            'image' => '/resources/megaphone_girl.png',
                            'link' => '/resources/toolkits',
                        ],
                        [
                            'id' => 4,
                            'category' => 'Guides & Manuals',
                            'title' => 'Social Media Advocacy: Amplifying Youth Voices',
                            'description' => 'Learn how to use digital platforms to raise awareness and support campaigns. This guide covers visual content creation, hashtag optimization, and community engagement strategies specifically tailored for young leaders.',
                            'image' => '/resources/resources_card.png',
                            'link' => '/resources/toolkits',
                        ],
                        [
                            'id' => 5,
                            'category' => 'Research Papers',
                            'title' => 'E-Cigarette Marketing Tactics Targeting Underage Audiences',
                            'description' => 'A deep-dive research paper analyzing modern marketing tactics used by tobacco and e-cigarette brands to attract children and teens on popular social media networks, and proposed regulatory responses.',
                            'image' => '/no-tobacco.jpg',
                            'link' => '/resources/knowledge-publications',
                        ],
                    ],
                ]
            ]
        );

        // Resources — Take Action CTA
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'cta'],
            [
                'title' => 'Resources Take Action CTA',
                'content' => [
                    'heading' => 'Ready to take action?',
                    'description' => 'Use our resources to build campaigns, mobilize communities, and demand accountability.',
                    'primary_btn_text' => 'Join the Movement',
                    'primary_btn_link' => '/campaigns',
                    'secondary_btn_text' => 'Read the Declaration',
                    'secondary_btn_link' => '/declarations',
                ]
            ]
        );

        // Resources — Home section tiles (also on homepage)
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'resource_tiles'],
            [
                'title' => 'Resource Tiles (Homepage Preview)',
                'content' => [
                    'heading_highlight' => 'Resources',
                    'heading_for' => 'for',
                    'heading_action' => 'Action',
                    'description' => 'Explore briefs, factsheets, letters, statements, and videos designed to support youth-led advocacy.',
                    'cta_text' => 'View all resources',
                    'cta_link' => '/resources',
                    'tiles' => [
                        ['title' => 'Briefs & Factsheets', 'description' => 'Concise briefs and factsheets to support advocacy.'],
                        ['title' => 'Letters & Statements', 'description' => 'Official letters and statements from GYV.'],
                        ['title' => 'Toolkits', 'description' => 'Campaign toolkits and guides for advocates.'],
                        ['title' => 'Media Assets', 'description' => 'Logos, images, and media for your campaigns.'],
                        ['title' => 'Research Papers', 'description' => 'Evidence-based research supporting GYV advocacy.'],
                        ['title' => 'Videos', 'description' => 'Video resources and campaign footage.'],
                    ],
                ]
            ]
        );





        // 6. Categories
        $newsCategory = Category::updateOrCreate(
            ['slug' => 'news-and-updates'],
            ['name' => 'News & Updates']
        );

        $pressCategory = Category::updateOrCreate(
            ['slug' => 'press-release'],
            ['name' => 'Press Release']
        );

        // 7. Articles
        Article::updateOrCreate(
            ['slug' => 'youth-rise-against-big-tobacco-sponsorships'],
            [
                'title' => 'Youth Rise Against Big Tobacco Sponsorships',
                'content' => '<p>Youth coalitions worldwide are gathering to express concerns about direct marketing and sponsorships aimed at underage crowds...</p><p>We call on regulatory systems to enforce strict checks on tobacco advertisements and electronic nicotine nicotine delivery systems.</p>',
                'excerpt' => 'Youth coalitions worldwide are gathering to protest marketing sponsorships aimed at youth.',
                'featured_image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768',
                'category_id' => $newsCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now(),
                'meta_title' => 'Youth Protest Tobacco sponsorships | GYV News',
                'meta_description' => 'Read about the global youth coalition protesting against big tobacco sponsorships and marketing tactics targeting minors.',
            ]
        );

        Article::updateOrCreate(
            ['slug' => 'global-youth-voices-statement-presented-at-cop10'],
            [
                'title' => 'Global Youth Voices Statement Presented at COP10',
                'content' => '<p>The dynamic youth delegation of Global Youth Voices formally presented their unified declaration to COP10 delegates...</p>',
                'excerpt' => 'GYV representatives deliver a strong statement demanding financial accountability for tobacco harms.',
                'featured_image' => 'https://api.builder.io/api/v1/image/assets/TEMP/a3eb1d19111b8d8595f583afcee61f7093186ab5?width=768',
                'category_id' => $pressCategory->id,
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(2),
                'meta_title' => 'GYV Statement at COP10 | Press Release',
                'meta_description' => 'The Global Youth Voices statement calls on COP10 delegates to enforce financial liability on tobacco companies.',
            ]
        );

        // 8. Events
        Event::updateOrCreate(
            ['title' => 'GYV Annual Advocacy Summit 2026'],
            [
                'description' => 'Join thousands of youth advocates from across the globe to learn advocacy strategies, campaign management, and digital activism.',
                'location' => 'Geneva, Switzerland / Hybrid',
                'start_time' => Carbon::now()->addMonths(1)->setHour(9)->setMinute(0),
                'end_time' => Carbon::now()->addMonths(1)->addDays(2)->setHour(17)->setMinute(0),
                'registration_link' => 'https://summit.gyv.org/register',
                'image_url' => 'https://api.builder.io/api/v1/image/assets/TEMP/a0aec7389b59c267fe9e6cb147a75e605ac97963?width=768',
                'status' => 'published'
            ]
        );

        Event::updateOrCreate(
            ['title' => 'Tobacco Control Coalition Meeting'],
            [
                'description' => 'Strategic planning session for member organizations to coordinate efforts for regional policy enhancements.',
                'location' => 'Virtual (Zoom)',
                'start_time' => Carbon::now()->addWeeks(2)->setHour(14)->setMinute(0),
                'end_time' => Carbon::now()->addWeeks(2)->setHour(16)->setMinute(0),
                'registration_link' => 'https://zoom.us/j/gyvmeeting',
                'image_url' => 'https://api.builder.io/api/v1/image/assets/TEMP/a3eb1d19111b8d8595f583afcee61f7093186ab5?width=768',
                'status' => 'published'
            ]
        );

        // 9. Settings
        Setting::set('organization_name', 'Global Youth Voices');
        Setting::set('contact_email', 'info@globalyouthvoices.org');
        Setting::set('phone_number', '+41 22 730 0111');
        Setting::set('address', 'Geneva, Switzerland');
        Setting::set('social_links', [
            'facebook' => 'https://facebook.com/globalyouthvoices',
            'twitter' => 'https://twitter.com/globalyouthvoices',
            'instagram' => 'https://instagram.com/globalyouthvoices',
            'youtube' => 'https://youtube.com/globalyouthvoices'
        ]);
        Setting::set('branding', [
            'logo' => '/logo.png',
            'logo_dark' => '/logo-dark.png',
            'favicon' => '/favicon.ico'
        ]);
    }
}
