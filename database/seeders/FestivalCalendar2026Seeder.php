<?php

namespace Database\Seeders;

use App\Models\SocialFestivalEvent;
use Illuminate\Database\Seeder;

class FestivalCalendar2026Seeder extends Seeder
{
    public function run(): void
    {
        // Remove previously seeded country-specific entries outside India and
        // the general international calendar before syncing the approved set.
        SocialFestivalEvent::whereIn('name', [
            'Martin Luther King Jr. Day',
            'Lunar New Year',
            "St. Patrick's Day",
            'Nowruz',
            "Father's Day",
            'Thanksgiving Day',
        ])->delete();

        $events = [
            // India: national, religious and regional festivals.
            ['2026-01-01', "New Year's Day", 'National / Festival', 'India', 'Fixed-date New Year campaign opportunity.'],
            ['2026-01-13', 'Lohri', 'Indian / Regional', 'India', 'Punjabi winter harvest festival.'],
            ['2026-01-14', 'Makar Sankranti / Pongal', 'Indian / Regional', 'India', 'Harvest festival observed across India; includes Pongal in Tamil Nadu.'],
            ['2026-01-15', 'Pongal', 'Indian / Regional', 'Tamil Nadu, India', 'Thai Pongal main day.'],
            ['2026-01-23', 'Vasant Panchami / Saraswati Puja', 'Indian / Hindu', 'India', 'Festival of learning and the arrival of spring.'],
            ['2026-01-26', 'Republic Day', 'National', 'India', 'Indian national holiday.'],
            ['2026-02-01', 'Guru Ravidas Jayanti', 'Indian / Sikh', 'India', 'Birth anniversary observance.'],
            ['2026-02-15', 'Maha Shivratri', 'Indian / Hindu', 'India', 'Major Hindu festival; lunar date may vary by location.'],
            ['2026-02-19', 'Shivaji Jayanti', 'Indian / Regional', 'Maharashtra, India', 'Birth anniversary of Chhatrapati Shivaji Maharaj.'],
            ['2026-03-03', 'Holika Dahan', 'Indian / Hindu', 'India', 'Bonfire observance on the eve of Holi.'],
            ['2026-03-04', 'Holi', 'Indian / Hindu', 'India', 'Festival of colours.'],
            ['2026-03-19', 'Ugadi / Gudi Padwa / Cheti Chand', 'Indian / Regional', 'India', 'Regional New Year festivals; date based on the lunisolar calendar.'],
            ['2026-03-20', 'Jamat-ul-Vida', 'Indian / Muslim', 'India', 'Last Friday of Ramadan; date-sensitive observance.'],
            ['2026-03-21', 'Eid al-Fitr', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date by one day.'],
            ['2026-03-26', 'Ram Navami', 'Indian / Hindu', 'India', 'Birth anniversary of Lord Rama.'],
            ['2026-04-02', 'Mahavir Jayanti', 'Indian / Jain', 'India', 'Birth anniversary of Lord Mahavira.'],
            ['2026-04-03', 'Good Friday', 'Indian / Christian', 'India', 'Christian observance.'],
            ['2026-04-05', 'Easter Sunday', 'Indian / Christian', 'India', 'Christian festival.'],
            ['2026-04-14', 'Ambedkar Jayanti / Vaisakhi / Vishu / Tamil New Year', 'Indian / Regional', 'India', 'Multiple regional and national observances.'],
            ['2026-04-15', 'Bohag Bihu / Pohela Boishakh', 'Indian / Regional', 'Assam / West Bengal, India', 'Regional New Year and harvest observances.'],
            ['2026-05-01', 'Buddha Purnima', 'Indian / Buddhist', 'India', 'Buddhist festival; date may vary by tradition.'],
            ['2026-05-27', 'Eid al-Adha / Bakrid', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date by one day.'],
            ['2026-06-21', 'International Day of Yoga', 'International / India', 'India', 'United Nations observance with major Indian campaigns.'],
            ['2026-06-26', 'Muharram / Ashura', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date.'],
            ['2026-07-16', 'Rath Yatra', 'Indian / Hindu', 'India', 'Chariot festival, especially associated with Puri.'],
            ['2026-07-29', 'Guru Purnima', 'Indian / Hindu / Buddhist', 'India', 'Teachers and gurus observance.'],
            ['2026-08-15', 'Independence Day', 'National', 'India', 'Indian national holiday.'],
            ['2026-08-15', 'Parsi New Year / Navroz', 'Indian / Zoroastrian', 'India', 'Parsi New Year observance.'],
            ['2026-08-26', 'Onam / Thiruvonam', 'Indian / Regional', 'Kerala, India', 'Kerala harvest festival.'],
            ['2026-08-26', 'Milad-un-Nabi', 'Indian / Muslim', 'India', 'Expected date; moon sighting may shift the date.'],
            ['2026-08-28', 'Raksha Bandhan', 'Indian / Hindu', 'India', 'Sibling celebration.'],
            ['2026-09-04', 'Janmashtami', 'Indian / Hindu', 'India', 'Birth anniversary of Lord Krishna.'],
            ['2026-09-14', 'Ganesh Chaturthi', 'Indian / Hindu', 'India', 'Festival of Lord Ganesha.'],
            ['2026-10-02', 'Mahatma Gandhi Jayanti', 'National', 'India', 'Indian national observance.'],
            ['2026-10-11', 'Sharad Navratri Begins', 'Indian / Hindu', 'India', 'Nine-night festival begins.'],
            ['2026-10-17', 'Durga Puja Begins', 'Indian / Regional', 'India', 'Durga Puja festivities begin.'],
            ['2026-10-19', 'Maha Ashtami', 'Indian / Hindu', 'India', 'Durga Puja/Navratri observance.'],
            ['2026-10-20', 'Dussehra / Vijayadashami', 'Indian / Hindu', 'India', 'Victory of good over evil.'],
            ['2026-10-29', 'Karwa Chauth', 'Indian / Hindu', 'India', 'Married women’s fasting and family observance.'],
            ['2026-11-08', 'Diwali / Deepavali / Lakshmi Puja', 'Indian / Hindu / Jain / Sikh', 'India', 'Festival of lights; date is location and moon-sighting sensitive.'],
            ['2026-11-09', 'Govardhan Puja', 'Indian / Hindu', 'India', 'Day after Diwali.'],
            ['2026-11-11', 'Bhai Dooj', 'Indian / Hindu', 'India', 'Sibling celebration after Diwali.'],
            ['2026-11-14', "Children's Day", 'National / Observance', 'India', 'Indian children’s observance.'],
            ['2026-11-15', 'Chhath Puja', 'Indian / Hindu / Regional', 'India', 'Sun worship festival, especially in Bihar, Jharkhand and eastern Uttar Pradesh.'],
            ['2026-11-24', 'Guru Nanak Jayanti', 'Indian / Sikh', 'India', 'Birth anniversary of Guru Nanak Dev Ji.'],
            ['2026-12-25', 'Christmas Day', 'Indian / Christian', 'India', 'Christian festival and national holiday.'],

            // International festivals and widely used global campaign dates.
            ['2026-01-01', "New Year's Day", 'International / Festival', 'International', 'Global New Year observance.'],
            ['2026-02-14', "Valentine's Day", 'International / Commercial', 'International', 'Global relationship and gifting campaign date.'],
            ['2026-02-18', 'Ramadan Begins', 'International / Muslim', 'International', 'Expected start; local moon sighting may shift the date.'],
            ['2026-03-08', "International Women's Day", 'International / UN', 'International', 'Global equality and women’s empowerment observance.'],
            ['2026-04-01', "April Fool's Day", 'International / Cultural', 'International', 'Widely observed social and marketing date.'],
            ['2026-04-22', 'Earth Day', 'International / Environmental', 'International', 'Environmental awareness campaign date.'],
            ['2026-05-01', 'International Workers’ Day / Labour Day', 'International / Observance', 'International', 'Workers’ rights and labour observance.'],
            ['2026-05-10', "Mother's Day", 'International / Commercial', 'International', 'Common second-Sunday-of-May observance; country dates vary.'],
            ['2026-06-05', 'World Environment Day', 'International / UN', 'International', 'United Nations environmental observance.'],
            ['2026-06-30', 'International Asteroid Day', 'International / UN', 'International', 'Science and space awareness observance.'],
            ['2026-07-30', 'International Day of Friendship', 'International / UN', 'International', 'United Nations friendship observance.'],
            ['2026-08-12', 'International Youth Day', 'International / UN', 'International', 'United Nations youth observance.'],
            ['2026-09-21', 'International Day of Peace', 'International / UN', 'International', 'United Nations peace observance.'],
            ['2026-10-31', 'Halloween', 'International / Cultural', 'International', 'Global cultural and commercial campaign date.'],
            ['2026-12-05', 'Hanukkah Begins', 'International / Jewish', 'International', 'Jewish festival; begins at sunset and date may vary by timezone.'],
            ['2026-12-24', 'Christmas Eve', 'International / Christian', 'International', 'Christmas campaign and observance date.'],
            ['2026-12-25', 'Christmas Day', 'International / Christian', 'International', 'Global Christian festival.'],
            ['2026-12-31', "New Year's Eve", 'International / Festival', 'International', 'Year-end campaign and celebration date.'],
        ];

        foreach ($events as [$date, $name, $category, $region, $description]) {
            SocialFestivalEvent::updateOrCreate(
                ['name' => $name, 'event_date' => $date, 'country_region' => $region],
                [
                    'category' => $category,
                    'recurrence_type' => 'yearly',
                    'client_id' => null,
                    'description' => $description . ' Calendar year: 2026.',
                    'reference_url' => null,
                ]
            );
        }

        $this->command?->info(count($events) . ' 2026 festival and observance records seeded.');
    }
}
