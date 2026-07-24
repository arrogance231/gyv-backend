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
            'publish-content' => 'Publish page changes and drafts',
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
                    'title' => 'Young voices.',
                    'subtitle' => 'Shared demands.',
                    'highlight' => 'A tobacco-free future.',
                    'description' => 'Global Youth Voices is a global movement of youth organizations, coalitions, and advocates working together to protect young people from tobacco and nicotine industry harm and demand accountability from the companies responsible.',
                    'primary_cta_text' => 'Read the Declaration',
                    'primary_cta_link' => '/declarations',
                    'secondary_cta_text' => 'About GYV',
                    'secondary_cta_link' => '/about-us',
                    'bg_image' => '/homepage/hero/bg.jpg',
                ]
            ]
        );

        // Home Youth Movement Info Section
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'movement'],
            [
                'title' => 'Youth Movement Info Section',
                'content' => [
                    'card1_title' => 'What is Global Youth Voices?',
                    'card1_p1' => 'Global Youth Voices, or GYV, brings together youth organizations, coalitions, and advocates from different regions around a shared call for stronger tobacco control and tobacco industry accountability.',
                    'card1_p2' => 'GYV connects young people across countries so that their experiences, evidence, and demands can be heard in the global policy spaces where decisions affecting their health and future are made.',
                    'card1_link_text' => 'Learn more about GYV',
                    'card1_link_url' => '/about-us',
                    'card2_title' => 'Why does GYV matter?',
                    'card2_p1' => 'Young people remain a central target of the tobacco and nicotine industry. Flavors, attractive product designs, influencer promotion, digital marketing, and misleading claims about innovation and harm reduction are used to make addiction appear normal, modern, and harmless.',
                    'card2_p2' => 'GYV helps young people respond collectively—not only by raising awareness, but by calling for laws, policies, and accountability measures that protect present and future generations.',
                    'card2_link_text' => 'Learn why we exist',
                    'card2_link_url' => '/about-us#why-we-exist',
                ]
            ]
        );

        // Home Youth Declaration Section
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'declaration'],
            [
                'title' => 'Our Declaration Section',
                'content' => [
                    'title_prefix' => 'Our',
                    'highlight' => 'Declaration',
                    'description' => 'The Global Youth Voices Declaration is the foundation of our movement. It expresses the shared demands of young people for a healthier, fairer, and more accountable future.',
                    'cta_text' => 'Read the full Declaration',
                    'cta_link' => '/declarations',
                    'card1_title' => 'Protect young people',
                    'card1_body' => 'End industry practices, products, and promotions that create addiction and cause harm to young people.',
                    'card2_title' => 'Protect public policy',
                    'card2_body' => 'Keep tobacco control decisions free from tobacco industry interference and conflicts of interest.',
                    'card3_title' => 'Demand accountability',
                    'card3_body' => 'Hold tobacco companies financially and legally responsible for the health, social, environmental, and intergenerational harms they cause.',
                    'card4_title' => 'Include young people',
                    'card4_body' => 'Ensure that young people participate meaningfully and independently in decisions affecting their health and future.',
                ]
            ]
        );

        // Home Global Reach Section
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'reach'],
            [
                'title' => 'Global Reach Section',
                'content' => [
                    'title_prefix' => 'One movement.',
                    'highlight' => 'Global',
                    'title_suffix' => 'reach.',
                    'description_line1' => 'GYV brings together diverse youth organizations, coalitions, and advocates while allowing each participating group to retain its own identity, experience, and area of expertise.',
                    'description_line2' => 'Together, we connect country and regional realities with a shared global call for bold action.',
                    'cta_text' => 'Explore Our Global Network',
                    'cta_link' => '/about-us',
                    'stat1_value' => '77',
                    'stat1_label' => 'Participating youth organizations and coalitions',
                    'stat2_value' => '10',
                    'stat2_label' => 'Regional youth spokespersons',
                    'stat3_value' => '6',
                    'stat3_label' => 'WHO regions represented',
                    'stat4_value' => '135+',
                    'stat4_label' => 'Countries reached through participating organizations and networks',
                ]
            ]
        );

        // Home Latest Stories & Resources Section
        PageSection::updateOrCreate(
            ['page_id' => $homePage->id, 'section_key' => 'stories'],
            [
                'title' => 'Latest Stories & Resources Section',
                'content' => [
                    'highlight' => 'Latest',
                    'title_part1' => 'Stories &',
                    'title_part2' => 'Resources',
                    'description' => 'Explore recent youth statements, campaign updates, advocacy stories, videos, and practical materials from across the GYV movement.',
                    'cta_text' => 'View all Stories & Resources',
                    'cta_link' => '/resources',
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

        // Campaigns — Explore Section
        PageSection::updateOrCreate(
            ['page_id' => $campaignsPage->id, 'section_key' => 'explore'],
            [
                'title' => 'Explore Our Campaigns',
                'content' => [
                    'heading_prefix' => 'Explore Our',
                    'highlight' => 'Campaigns',
                    'subtitle' => 'Browse current and previous GYV campaigns. Each campaign page brings together its objectives, messages, statements, youth contributions, advocacy materials, and results.',
                    'campaigns' => [
                        [
                            'tagline' => 'INTERNATIONAL YOUTH DAY 2026',
                            'title' => 'Our Future Is Not Their Market',
                            'description' => 'Young people from different contexts unite around shared demands to prepare for COP11, protect policymaking from tobacco industry interference, prevent nicotine addiction, and make the tobacco industry pay.',
                            'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80',
                            'slug' => 'our-future-is-not-their-market',
                            'year' => '2026',
                            'category' => 'International Youth Day',
                            'policy_issue' => 'Industry Interference',
                        ],
                        [
                            'tagline' => 'WORLD NO TOBACCO DAY 2026',
                            'title' => 'Unmasking the Appeal: Youth Counter Addiction and Demand Accountability',
                            'description' => 'A global youth campaign exposing how tobacco and nicotine products are designed, marketed, and positioned to create addiction among young people.',
                            'image' => '/no-tobacco.jpg',
                            'slug' => 'unmasking-the-appeal',
                            'year' => '2026',
                            'category' => 'World No Tobacco Day',
                            'policy_issue' => 'Flavors & Addiction',
                        ],
                        [
                            'tagline' => 'COP11 2025',
                            'title' => 'Youth Demands at COP11',
                            'description' => 'GYV brought youth calls for stronger implementation of WHO FCTC Article 5.3, tobacco industry liability, action on environmental harm, and ambitious tobacco-control measures to Geneva.',
                            'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
                            'slug' => 'youth-demands-at-cop11',
                            'year' => '2025',
                            'category' => 'COP Policy',
                            'policy_issue' => 'FCTC Article 5.3',
                        ],
                        [
                            'tagline' => 'INTERNATIONAL YOUTH DAY 2025',
                            'title' => 'Youth Voices for a Tobacco-Free and Plastic-Free Future',
                            'description' => 'GYV connected International Youth Day with global plastics negotiations and called for cigarette filters to be addressed, the tobacco industry to be excluded, and polluters to be held accountable.',
                            'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=800&q=80',
                            'slug' => 'youth-voices-tobacco-free-plastic-free',
                            'year' => '2025',
                            'category' => 'International Youth Day',
                            'policy_issue' => 'Environmental Impact',
                        ],
                        [
                            'tagline' => 'WORLD CONFERENCE ON TOBACCO CONTROL 2025',
                            'title' => 'Transforming the Future',
                            'description' => 'Young advocates called on the global tobacco-control community to move from promises to action and place youth participation and accountability at the centre of tobacco control.',
                            'image' => '/homepage/hero/bg.jpg',
                            'slug' => 'transforming-the-future',
                            'year' => '2025',
                            'category' => 'Global Conference',
                            'policy_issue' => 'Youth Participation',
                        ],
                        [
                            'tagline' => 'WORLD NO TOBACCO DAY 2025',
                            'title' => 'Unmasking the Tobacco Industry\'s Appeal',
                            'description' => 'A coordinated youth campaign exposing how the tobacco industry uses flavours, digital marketing, lifestyle branding, and misleading claims to attract a new generation.',
                            'image' => '/no-tobacco.jpg',
                            'slug' => 'unmasking-industry-appeal-2025',
                            'year' => '2025',
                            'category' => 'World No Tobacco Day',
                            'policy_issue' => 'Flavors & Marketing',
                        ],
                    ],
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
        $declarations = [];
        for ($i = 1; $i <= 9; $i++) {
            $num = str_pad($i, 2, '0', STR_PAD_LEFT);
            $declarations[] = [
                'slug' => "rec-role-$num",
                'title' => 'Recognizing the role of Tobacco Industry',
                'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'details' => []
            ];
        }

        PageSection::updateOrCreate(
            ['page_id' => $declarationsPage->id, 'section_key' => 'first_declaration'],
            [
                'title' => 'First Declaration Block',
                'content' => [
                    'declarations' => $declarations
                ]
            ]
        );

        // Delete second declaration block if it exists to keep only the 9 items in the first block
        PageSection::where('page_id', $declarationsPage->id)->where('section_key', 'second_declaration')->delete();

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
                    'statement' => '<strong>Global Youth Voices</strong> is a youth movement convened by the <span class="text-[#0E85B1] font-bold">Global Center for Good Governance in Tobacco Control</span> that brings together <em class="font-semibold text-[#42516F]">youth organizations, coalitions,</em> and <em class="font-semibold text-[#42516F]">young advocates</em> working to <span class="border-b-[3px] border-[#fecf02] pb-0.5 font-bold">protect public health</span> from tobacco industry interference.',
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
                        [
                            'name' => 'Yvette Mbewe',
                            'organization' => 'TOFAZA Youth Advocates',
                            'country' => 'Zambia',
                            'region' => 'AFRO',
                            'image' => '/homepage/hero/avatar.svg',
                        ],
                        [
                            'name' => 'Lesego Mateme',
                            'organization' => 'South Africa Tobacco Free Youth Forum',
                            'country' => 'South Africa',
                            'region' => 'AFRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Lisa Lu',
                            'organization' => 'IYTC',
                            'country' => 'US',
                            'region' => 'AMRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Juan Herrera',
                            'organization' => 'Alianza Juvenil',
                            'country' => 'Colombia',
                            'region' => 'AMRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Ana Paula Ramis',
                            'organization' => 'RIPO',
                            'country' => 'Mexico',
                            'region' => 'AMRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Mehrbanoo Hosseinirad',
                            'organization' => 'IMSA Iran',
                            'country' => 'Iran',
                            'region' => 'AMRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Sama Ghozlan',
                            'organization' => 'IPSF',
                            'country' => 'Jordan',
                            'region' => 'EMRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Karina Mocanu',
                            'organization' => 'ENSP Next',
                            'country' => 'Belgium',
                            'region' => 'EURO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Hilario de los Santos',
                            'organization' => 'Healthy Philippines Youth Network Alliance',
                            'country' => 'Philippines',
                            'region' => 'WPRO',
                            'image' => null,
                        ],
                        [
                            'name' => 'Sara Ruzana',
                            'organization' => 'ASEAN Youth Network',
                            'country' => 'Indonesia',
                            'region' => 'WPRO',
                            'image' => null,
                        ],
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
                    'heading_prefix' => 'Our',
                    'highlight' => 'Global',
                    'heading_suffix' => 'Network',
                    'p1' => 'GYV brings together youth-led and youth-serving organizations, professional associations, coalitions, and advocates from different parts of the world.',
                    'p2' => 'Participating organizations contribute according to their experience and capacity. Some lead campaigns, develop technical messages, or participate in policy discussions. Others translate materials, engage local decision-makers, share country perspectives, or amplify global calls through their networks.',
                    'p3' => 'What connects the network is not organizational size, but commitment to independent, evidence-informed, and ambitious tobacco-control advocacy.',
                    'partners_title' => 'Participating Organizations and Coalitions',
                    'partners_subtitle' => 'Explore the organizations contributing to the Global Youth Voices movement.',
                    'stats' => [
                        [
                            'value' => '77',
                            'title' => 'Participating organizations and coalitions',
                            'subtitle' => 'Working across youth, health, development, education, environment, and related fields.',
                        ],
                        [
                            'value' => '10',
                            'title' => 'Regional youth spokespersons',
                            'subtitle' => 'Supporting coordination and representation across regions.',
                        ],
                        [
                            'value' => '6',
                            'title' => 'WHO regions',
                            'subtitle' => 'Connecting different policy, social, and cultural contexts.',
                        ],
                        [
                            'value' => '130',
                            'title' => 'And more countries reached',
                            'subtitle' => 'Through the combined networks of participating organizations.',
                        ],
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

        // About — Our Journey (Milestones Timeline)
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'journey'],
            [
                'title' => 'Our Journey',
                'content' => [
                    'heading_prefix' => 'Our',
                    'highlight' => 'Journey',
                    'description' => 'GYV continues to grow through the collective work of youth organizations and advocates who have brought shared demands into campaigns and policy spaces around the world.',
                    'cta_text' => 'Explore GYV Campaigns',
                    'cta_link' => '/campaigns',
                    'milestones' => [
                        [
                            'year' => '2024',
                            'title' => 'A shared youth position',
                            'description' => 'The Global Youth Voices Declaration was launched as a unified call for bold tobacco-control action, protection from industry interference, and tobacco industry accountability.',
                            'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'year' => '2025',
                            'title' => 'Regional Advocacy Expansion',
                            'description' => 'Expanded regional spokespersons networks across WHO AFRO, AMRO, EMRO, EURO, and WPRO regions, driving youth-led policy workshops and campaign responses.',
                            'image' => '/homepage/hero/bg.jpg',
                        ],
                        [
                            'year' => '2025',
                            'title' => 'COP Global Youth Mobilization',
                            'description' => 'Consolidated youth representatives from over 30 countries to deliver statements and push for strict tobacco industry accountability measures at global WHO FCTC convenings.',
                            'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'year' => '2026',
                            'title' => 'World No Tobacco Day 2026 Campaign',
                            'description' => 'Mobilized over 77 youth coalitions worldwide to expose predatory nicotine marketing tactics and demand smoke-free environments for future generations.',
                            'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=800&q=80',
                        ],
                        [
                            'year' => '2026',
                            'title' => 'Global Youth Network Scaling',
                            'description' => 'Reached 130+ countries through combined member networks, connecting local grassroots actions directly into international public health decision-making.',
                            'image' => '/homepage/hero/bg.jpg',
                        ],
                    ],
                ]
            ]
        );

        // About — Work with Global Youth Voices
        PageSection::updateOrCreate(
            ['page_id' => $aboutPage->id, 'section_key' => 'join'],
            [
                'title' => 'Work with Global Youth Voices',
                'content' => [
                    'heading_prefix' => 'Work with Global',
                    'highlight' => 'Youth',
                    'heading_suffix' => 'Voices',
                    'p1' => 'GYV welcomes collaboration with credible and independent youth organizations, coalitions, advocates, and public health partners that share our commitment to stronger tobacco control.',
                    'p2' => 'Working with GYV may include contributing to campaigns, sharing country perspectives, developing youth-friendly advocacy materials, participating in policy opportunities, translating messages, or amplifying collective calls.',
                    'p3' => 'Participation is subject to conflict-of-interest review.',
                    'cta_text' => 'Partner with GYV',
                    'cta_link' => '/contact',
                ]
            ]
        );

        // ══════════════════════════════════════════
        // CONTACT PAGE — all 5 sections
        // ══════════════════════════════════════════
        $contactPage = Page::updateOrCreate(
            ['slug' => 'contact'],
            [
                'title' => 'Contact Us',
                'meta_title' => 'Partner with Global Youth Voices | Contact Us',
                'meta_description' => 'Join a global network of independent youth organizations, coalitions, and advocates working for stronger tobacco control and tobacco industry accountability.',
            ]
        );

        // Contact — Hero Section
        PageSection::updateOrCreate(
            ['page_id' => $contactPage->id, 'section_key' => 'hero'],
            [
                'title' => 'Contact Hero',
                'content' => [
                    'highlight' => 'Partner',
                    'heading_line1_suffix' => 'with Global',
                    'heading_line2' => 'Youth Voices',
                    'description' => 'Join a global network of independent youth organizations, coalitions, and advocates working for stronger tobacco control and tobacco industry accountability.',
                    'illustration' => '/events.png',
                ]
            ]
        );

        // Contact — Partner Overview Section
        PageSection::updateOrCreate(
            ['page_id' => $contactPage->id, 'section_key' => 'partner_overview'],
            [
                'title' => 'Partner Overview',
                'content' => [
                    'col1_title_part1' => 'Who We',
                    'col1_title_highlight' => 'Work',
                    'col1_title_part2' => 'With',
                    'col1_subtitle' => 'GYV works with:',
                    'who_we_work_with' => [
                        ['text' => 'Youth-led and youth-serving organizations', 'color' => 'yellow'],
                        ['text' => 'Regional and global youth networks', 'color' => 'cyan'],
                        ['text' => 'Student and professional associations', 'color' => 'yellow'],
                        ['text' => 'Public health and tobacco-control advocates', 'color' => 'cyan'],
                        ['text' => 'Independent young advocates with relevant experience', 'color' => 'yellow'],
                        ['text' => 'Institutions supporting meaningful youth participation', 'color' => 'cyan'],
                    ],
                    'col2_title_highlight' => 'Ways',
                    'col2_title_part2' => 'to Contribute',
                    'col2_subtitle' => 'Partnership may include:',
                    'ways_to_contribute' => [
                        ['text' => 'Contributing to global campaigns', 'color' => 'cyan'],
                        ['text' => 'Sharing country or regional perspectives', 'color' => 'yellow'],
                        ['text' => 'Participating in interviews, webinars, and policy events', 'color' => 'cyan'],
                        ['text' => 'Translating or adapting campaign materials', 'color' => 'yellow'],
                        ['text' => 'Engaging policymakers or media', 'color' => 'cyan'],
                        ['text' => 'Amplifying GYV statements', 'color' => 'yellow'],
                        ['text' => 'Helping develop youth-friendly resources', 'color' => 'cyan'],
                    ],
                ]
            ]
        );

        // Contact — Independence Matters Section
        PageSection::updateOrCreate(
            ['page_id' => $contactPage->id, 'section_key' => 'independence_matters'],
            [
                'title' => 'Independence Matters',
                'content' => [
                    'title_part1' => 'Our',
                    'title_highlight' => 'Independence',
                    'title_part2' => 'Matters',
                    'body_p1' => 'There is a fundamental and irreconcilable conflict between public health interests and the interests of the tobacco industry.',
                    'body_p2' => 'GYV does not engage with the tobacco industry or with organizations and individuals furthering its interests. All partnership and participation requests are reviewed for potential conflicts of interest.',
                    'body_p3' => 'Submitting an expression of interest does not automatically result in participation.',
                    'checkbox_label' => 'I confirm that I have read the GYV conflict-of-interest statement and that neither I nor my organization has a relationship with the tobacco industry or entities furthering its interests.',
                ]
            ]
        );

        // Contact — Contact Form Info
        PageSection::updateOrCreate(
            ['page_id' => $contactPage->id, 'section_key' => 'form_info'],
            [
                'title' => 'Form Info & Feedback',
                'content' => [
                    'success_title' => 'Expression of Interest Received!',
                    'success_description' => 'Thank you for submitting your expression of interest to Global Youth Voices. Our team will review your application and conflict-of-interest declaration shortly.',
                    'reset_button_text' => 'Submit Another Form',
                ]
            ]
        );

        // Contact — Take Action CTA
        PageSection::updateOrCreate(
            ['page_id' => $contactPage->id, 'section_key' => 'take_action_cta'],
            [
                'title' => 'Contact Take Action CTA',
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

        // Resources — Knowledge & Publications sub-page items
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'knowledge_publications_items'],
            [
                'title' => 'Knowledge & Publications Items',
                'content' => [
                    'heading' => 'Briefs and Factsheets',
                    'description' => 'Briefs and factsheets to help advocates and partners.',
                    'items' => [
                        [
                            'title' => 'Youth Advocacy Toolkit',
                            'category' => 'Brief',
                            'description' => 'A practical guide for youth organizations to translate evidence into policy action.',
                            'image' => '',
                            'link' => '/resources/knowledge-publications/youth-advocacy-toolkit',
                        ],
                        [
                            'title' => 'Policy Brief: School Protections',
                            'category' => 'Brief',
                            'description' => 'A concise policy brief designed for decision-makers on campus tobacco controls.',
                            'image' => '',
                            'link' => '/resources/knowledge-publications/policy-brief-school-protections',
                        ],
                        [
                            'title' => 'Factsheet: Youth Vaping Trends',
                            'category' => 'Factsheet',
                            'description' => 'A clear data summary of youth vaping prevalence, risk factors, and policy implications.',
                            'image' => '',
                            'link' => '/resources/knowledge-publications/factsheet-youth-vaping-trends',
                        ],
                    ],
                ]
            ]
        );

        // Resources — Media sub-page items
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'media_items'],
            [
                'title' => 'Media Items',
                'content' => [
                    'heading' => 'Media',
                    'description' => 'Press articles, photo libraries, and short videos for media outreach and storytelling.',
                    'items' => [
                        [
                            'title' => 'Press: Youth Stories',
                            'category' => 'Article',
                            'description' => 'A feature article highlighting young advocates, data, and policy progress.',
                            'image' => '',
                            'link' => '/resources/media/press-youth-stories',
                        ],
                        [
                            'title' => 'Short Film: Voices for Health',
                            'category' => 'Video',
                            'description' => 'A short advocacy film with testimony, context, and calls to action.',
                            'image' => '',
                            'link' => '/resources/media/short-film-voices-for-health',
                        ],
                    ],
                ]
            ]
        );

        // Resources — Official Communications sub-page items
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'official_communications_items'],
            [
                'title' => 'Official Communications Items',
                'content' => [
                    'heading' => 'Letters and Statements',
                    'description' => 'Letters, statements, and formal communications addressed to governments and institutions.',
                    'items' => [
                        [
                            'title' => 'Open Letter to Ministers',
                            'category' => '2025-11-12',
                            'description' => 'A formal government letter requesting urgent policy action on youth tobacco use.',
                            'image' => '',
                            'link' => '/resources/official-communications/open-letter-to-ministers',
                        ],
                        [
                            'title' => 'Statement on Flavored Products',
                            'category' => '2026-02-03',
                            'description' => 'An official communication calling for prohibition of youth-targeted flavored tobacco products.',
                            'image' => '',
                            'link' => '/resources/official-communications/statement-on-flavored-products',
                        ],
                    ],
                ]
            ]
        );

        // Resources — Toolkits sub-page items
        PageSection::updateOrCreate(
            ['page_id' => $resourcesPage->id, 'section_key' => 'toolkits_items'],
            [
                'title' => 'Toolkits Items',
                'content' => [
                    'heading' => 'Toolkits & Campaign Info',
                    'description' => 'Practical toolkits, campaign packs, and how-to guides for organizers.',
                    'items' => [
                        [
                            'title' => 'Youth Organizer Starter Kit',
                            'category' => '',
                            'description' => 'A step-by-step pack to start a campus campaign, with templates and outreach guidance.',
                            'image' => '',
                            'link' => '/campaigns',
                        ],
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
            'logo' => '/homepage/hero/gyv-logo.svg',
            'logo_dark' => '/homepage/hero/gyv-logo.svg',
            'favicon' => '/favicon.ico'
        ]);
    }
}
