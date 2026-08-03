<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    /**
     * Seed realistic published journal content for local development,
     * demonstrations, browser testing, and administrator training.
     */
    public function run(): void
    {
        foreach ($this->posts() as $post) {
            BlogPost::query()->updateOrCreate(
                [
                    'slug' => $post['slug'],
                ],
                [
                    ...$post,
                    'is_published' => true,
                ],
            );
        }
    }

    /**
     * Return the deterministic Coast & Cay journal articles.
     *
     * Image fields are intentionally omitted so rerunning this seeder does not
     * remove images later uploaded by an administrator through Filament.
     *
     * @return list<array{
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     body: string,
     *     published_at: CarbonImmutable,
     *     meta_title: string,
     *     meta_description: string
     * }>
     */
    private function posts(): array
    {
        return [
            [
                'title' => 'Jerk Is a Method, Not Just a Sauce',
                'slug' => 'jerk-is-a-method-not-just-a-sauce',
                'excerpt' => 'The flavor begins with seasoning and time, but it is the fire, smoke, and patience at the grill that finish the work.',
                'body' => <<<'HTML'
<p>Jerk is sometimes reduced to a bottled sauce or a measure of heat. In the kitchen, it is closer to a complete method: seasoning, resting, grilling, and paying attention to how the food responds to the fire.</p>

<h2>The seasoning needs time</h2>

<p>Our jerk seasoning brings together scallion, thyme, allspice, garlic, ginger, chile, and citrus. The exact balance changes slightly depending on what is being cooked, but the seasoning is always given enough time to settle into the meat rather than sitting only on its surface.</p>

<h2>Heat should build flavor</h2>

<p>A good jerk dish is not hot simply for the sake of being hot. The chile should arrive alongside smoke, herbs, spice, and the natural richness of the chicken or fish. Guests can still taste the food underneath the seasoning.</p>

<h2>The grill finishes the dish</h2>

<p>The edges should caramelize, the skin should pick up smoke, and the inside should remain tender. That last stage is why jerk belongs at the grill rather than being treated only as a sauce added at the end.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    31,
                    9,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'Jerk Is a Method, Not Just a Sauce | Coast & Cay',
                'meta_description' => 'How Coast & Cay approaches jerk seasoning, resting time, balanced heat, smoke, and careful cooking over the grill.',
            ],
            [
                'title' => 'Rice and Peas Holds the Plate Together',
                'slug' => 'rice-and-peas-holds-the-plate-together',
                'excerpt' => 'It catches gravy, settles the heat, and gives grilled and slow-cooked dishes the foundation they need.',
                'body' => <<<'HTML'
<p>Rice and peas rarely asks for attention, but it is one of the first things people notice when it is missing. It gives the plate structure and makes everything beside it easier to enjoy.</p>

<h2>More than plain rice</h2>

<p>The rice cooks with beans, coconut, thyme, scallion, and gentle seasoning. Each part has a job: coconut adds roundness, herbs add fragrance, and the beans give the dish enough substance to stand beside a curry or braise.</p>

<h2>Made for gravy</h2>

<p>Oxtail gravy, curry sauce, and juices from the grill all find their way into the rice. It absorbs those flavors without losing its own character, which is why the plate feels incomplete when the portion is too small.</p>

<h2>A familiar starting point</h2>

<p>For someone ordering Caribbean food for the first time, rice and peas is an easy place to begin. Pair it with jerk chicken, curry goat, or grilled fish, then add plantain or vegetables for contrast.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    28,
                    9,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'Why Rice and Peas Holds the Plate Together | Coast & Cay',
                'meta_description' => 'A closer look at rice and peas, coconut, herbs, beans, and why this familiar side works so well with Caribbean dishes.',
            ],
            [
                'title' => 'What We Mean by a Caribbean Table',
                'slug' => 'welcome-to-coast-and-cay',
                'excerpt' => 'A good meal does not have to be complicated: something from the grill, something from the pot, a few sides, and enough room to pass everything around.',
                'body' => <<<'HTML'
<p>When we talk about a Caribbean table, we are not describing one dish or one island. We mean the kind of meal that invites another plate, another spoonful of rice, and one more person to pull up a chair.</p>

<h2>Start with what takes time</h2>

<p>Some of the food on our menu begins well before service. Jerk seasoning needs time on the chicken. Oxtail and goat need a slow cook. Sorrel needs to steep with ginger and spice. Those steps are not decoration; they are the reason the finished dish tastes settled rather than rushed.</p>

<h2>Let the sides do their job</h2>

<p>Rice and peas, plantains, callaloo, festival bread, and slaw are not afterthoughts. They cool the heat, catch the gravy, add texture, and turn a main dish into a complete meal.</p>

<h2>Order for the table</h2>

<p>A practical first order is one or two small plates, a grilled dish, a curry or braise, and a few sides to share. Add sorrel or limeade, then decide whether there is still room for rum cake.</p>

<p>That is the experience we are building at Coast &amp; Cay: careful cooking, an easy room, and food that makes sense when it is passed around.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    24,
                    10,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'What We Mean by a Caribbean Table | Coast & Cay',
                'meta_description' => 'How Coast & Cay approaches Caribbean cooking, shared plates, slow-cooked dishes, sides, and relaxed meals in Santa Monica.',
            ],
            [
                'title' => 'Sorrel, Ginger, and a Glass Served Cold',
                'slug' => 'sorrel-ginger-and-a-glass-served-cold',
                'excerpt' => 'Tart hibiscus, fresh ginger, citrus, and spice make sorrel refreshing enough for lunch and familiar enough for a celebration.',
                'body' => <<<'HTML'
<p>Sorrel has the deep color of a heavy drink, but the first sip is bright and tart. Made from dried hibiscus, it is refreshed with ginger, citrus, and warm spices before being chilled.</p>

<h2>It benefits from resting</h2>

<p>The ingredients need time together. Ginger brings heat, the hibiscus gives acidity and color, and spices such as clove or cinnamon soften after steeping. Rushing the process usually leaves one flavor standing apart from the rest.</p>

<h2>A useful drink with food</h2>

<p>Sorrel works especially well beside grilled or spicy dishes. Its acidity cuts through richer food, while the sweetness keeps the drink from becoming sharp.</p>

<h2>Not only for special occasions</h2>

<p>Many people associate sorrel with holidays and family gatherings. We respect that history while also serving it as an everyday option—a cold glass alongside lunch, dinner, or a plate shared with friends.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    20,
                    11,
                    30,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'Sorrel, Ginger, and a Glass Served Cold | Coast & Cay',
                'meta_description' => 'Discover how hibiscus, ginger, citrus, spice, sweetness, and resting time come together in a cold glass of Caribbean sorrel.',
            ],
            [
                'title' => 'Plantains from Green to Fully Ripe',
                'slug' => 'plantains-from-green-to-fully-ripe',
                'excerpt' => 'The same fruit can become crisp and savory or soft and caramelized depending on when it reaches the pan.',
                'body' => <<<'HTML'
<p>Plantains change dramatically as they ripen. A green plantain is firm, starchy, and mild. A ripe plantain becomes soft, fragrant, and naturally sweet. Neither version is better; they simply belong in different places.</p>

<h2>Green plantains stay savory</h2>

<p>When sliced and fried while green, plantains hold their shape and develop crisp edges. They work well with salt, sauces, and dishes that need a firm contrast.</p>

<h2>Ripe plantains caramelize</h2>

<p>As the skin darkens, the starch turns to sugar. In the pan, those sugars brown quickly, creating a soft center and deeply caramelized surface.</p>

<h2>Balance matters</h2>

<p>A few pieces of ripe plantain can soften the heat of jerk seasoning or add sweetness beside a salty braise. The portion does not need to be large. Its job is to change the rhythm of the plate between bites.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    16,
                    11,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'Plantains from Green to Fully Ripe | Coast & Cay',
                'meta_description' => 'Learn how green and ripe plantains differ in texture, sweetness, cooking method, and the role they play on a Caribbean plate.',
            ],
            [
                'title' => 'The Case for a Slow Sunday Dinner',
                'slug' => 'the-case-for-a-slow-sunday-dinner',
                'excerpt' => 'Some meals are built around efficiency. Sunday dinner is built around giving the pot enough time and keeping the table open.',
                'body' => <<<'HTML'
<p>A proper Sunday meal does not need to be formal, but it usually asks for time. The braise starts early, the rice waits under its lid, and somebody checks whether another chair is needed.</p>

<h2>Slow food creates its own schedule</h2>

<p>Oxtail, goat, and stewed meats cannot be hurried without changing the result. Low heat gives connective tissue time to soften and allows the seasoning to move through the sauce instead of remaining separate.</p>

<h2>The sides arrive together</h2>

<p>Rice, vegetables, plantain, and something fresh or acidic turn the main dish into a meal for the whole table. The goal is not a complicated arrangement. It is balance and enough food to serve another plate.</p>

<h2>Make room after the meal</h2>

<p>Sunday dinner tends to last beyond the last bite. Coffee appears, dessert is cut, and conversation continues. That unhurried feeling is part of the meal, not something that happens after it.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    12,
                    12,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'The Case for a Slow Sunday Dinner | Coast & Cay',
                'meta_description' => 'Why slow braises, shared sides, an open table, and unhurried conversation remain at the heart of a Caribbean Sunday meal.',
            ],
            [
                'title' => 'How to Build a Family-Style Order',
                'slug' => 'how-to-build-a-family-style-order',
                'excerpt' => 'Choose one dish from the grill, one from the pot, and enough sides to let everyone make a plate their own way.',
                'body' => <<<'HTML'
<p>Ordering for several people becomes easier when the meal is treated as a shared table rather than a separate entrée for every guest.</p>

<h2>Begin with two different mains</h2>

<p>Choose one grilled dish and one slow-cooked dish. Jerk chicken and curry goat, for example, give the table two distinct flavors and textures without making the order difficult to manage.</p>

<h2>Add sides with different jobs</h2>

<p>Rice and peas provides the base. Plantain brings sweetness. Slaw or vegetables add freshness. Festival bread or another small side gives everyone something to pick up between plates.</p>

<h2>Keep sauces and heat flexible</h2>

<p>When possible, place hotter sauces on the side. Guests can add more, while anyone who prefers a gentler plate still gets the herbs, smoke, and seasoning of the dish.</p>

<p>For four people, two mains and three or four sides are usually a sensible starting point. Add small plates when the group arrives hungry.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    8,
                    17,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'How to Build a Caribbean Family-Style Order | Coast & Cay',
                'meta_description' => 'A practical guide to choosing grilled dishes, braises, rice, plantain, vegetables, and sauces for a shared Caribbean meal.',
            ],
            [
                'title' => 'From the Grill: Fish, Citrus, and Herbs',
                'slug' => 'from-the-grill-fish-citrus-and-herbs',
                'excerpt' => 'Fresh fish needs less interference than people think: careful seasoning, a hot grill, and something bright at the end.',
                'body' => <<<'HTML'
<p>Fish from the grill should taste like fish first. The seasoning supports it, the fire adds character, and citrus brings the flavors back into focus before the plate reaches the table.</p>

<h2>Season without hiding</h2>

<p>Herbs, scallion, garlic, chile, and citrus work well because they add fragrance and acidity without covering the natural flavor of the fish. The seasoning should be noticeable, not heavy.</p>

<h2>Use enough heat</h2>

<p>A properly heated grill helps the surface cook quickly while protecting the moisture inside. Turning the fish too early or moving it constantly makes the process harder than it needs to be.</p>

<h2>Finish with something bright</h2>

<p>A squeeze of lime, a fresh herb dressing, or a crisp slaw keeps the plate lively. Pair the fish with rice, vegetables, or plantain, then let the grill remain the main flavor.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    7,
                    2,
                    11,
                    30,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'From the Grill: Fish, Citrus, and Herbs | Coast & Cay',
                'meta_description' => 'How Coast & Cay balances fresh fish with herbs, citrus, chile, careful seasoning, and the direct heat of the grill.',
            ],
            [
                'title' => 'What Goes into a Good Curry Goat',
                'slug' => 'what-goes-into-a-good-curry-goat',
                'excerpt' => 'The curry needs to cook into the meat and the meat needs enough time to soften without disappearing into the sauce.',
                'body' => <<<'HTML'
<p>Curry goat rewards patience. The seasoning can be fragrant and the sauce can look ready long before the meat has reached the tenderness the dish requires.</p>

<h2>Season beyond the surface</h2>

<p>The meat is seasoned before it reaches the pot so the curry, herbs, garlic, ginger, and aromatics have time to settle. That early step keeps the finished dish from tasting like meat and sauce assembled separately.</p>

<h2>Cook the curry properly</h2>

<p>The curry seasoning needs contact with heat and fat to open its aroma and lose any raw edge. From there, liquid is added gradually and the pot is allowed to work slowly.</p>

<h2>Know when to stop</h2>

<p>The meat should pull away easily but still hold together on the plate. The sauce should coat the rice rather than run across it. Served with rice and peas, plantain, or vegetables, it is a complete meal without unnecessary additions.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    6,
                    25,
                    12,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'What Goes into a Good Curry Goat | Coast & Cay',
                'meta_description' => 'A look at seasoning, properly cooked curry, slow braising, tenderness, and the sauce that makes curry goat worth the wait.',
            ],
            [
                'title' => 'Rum Cake and a Proper Last Course',
                'slug' => 'rum-cake-and-a-proper-last-course',
                'excerpt' => 'Dense, fragrant, and best served in a modest slice, rum cake ends the meal without needing a complicated presentation.',
                'body' => <<<'HTML'
<p>Rum cake is not meant to be light or forgettable. It is rich with fruit, spice, browned sugar, and rum, which is why a small slice can finish a full dinner properly.</p>

<h2>The flavor develops over time</h2>

<p>Dried fruit, citrus peel, spice, and rum benefit from resting together before baking. Afterward, the cake settles again, becoming more even and fragrant as the moisture moves through it.</p>

<h2>Serve a sensible portion</h2>

<p>After jerk, curry, rice, and sides, dessert does not need to be oversized. A modest slice with coffee is usually enough. The cake brings intensity rather than volume.</p>

<h2>Save room without planning too hard</h2>

<p>The best approach is to order dessert for the table and pass it around. Someone who said they were finished usually finds room for one bite, then another.</p>
HTML,
                'published_at' => CarbonImmutable::create(
                    2026,
                    6,
                    18,
                    18,
                    0,
                    0,
                    'America/Los_Angeles',
                ),
                'meta_title' => 'Rum Cake and a Proper Last Course | Coast & Cay',
                'meta_description' => 'Why fruit, spice, browned sugar, resting time, and a modest serving make rum cake a fitting end to a Caribbean meal.',
            ],
        ];
    }
}
