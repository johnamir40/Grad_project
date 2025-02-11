<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Str;


class UpperPart extends Component
{
    public $detailsPart=[]; 
    public $slug;

    public function goToNextPage(){
        
        Log::info('clicked');

    }
    
    public function loadDetailsPart()
    {
        $gameSlug=$this->slug;
        //dd($gameSlug);
        $detailsPartCleaned  = Http::withHeaders(config('services.igdb'))
            ->send('POST', 'https://api.igdb.com/v4/games?', 
            [
                'body' => "fields name , genres.name , rating , summary , first_release_date,aggregated_rating,
                involved_companies.company.name, platforms.abbreviation , slug , websites.* ,
                cover.url , videos.video_id;
                where slug=\"{$gameSlug}\";" 
            ]
            )->json();
       
         
        //dump($this->cleanView($detailsPartCleaned[0]));
        
        $this->detailsPart =$this->cleanView($detailsPartCleaned);
        //dump($this->detailsPart['coverImageUrl']);
        //{{$detailsPart['coverImageUrl']}}
    }

    public function render()
    {
        return view('livewire.upper-part');
    }

    private function cleanView($games){
        return collect($games)->map(function ($game){
            return collect($game)->merge([
                'coverImageUrl'=>Str::replaceFirst('thumb','cover_big', $game['cover']['url']),
                'rating'=>isset($game['rating'])?round($game['rating']).'%':'0%',
                'aggregated_rating'=>isset($game['aggregated_rating'])?round($game['aggregated_rating']).'%':'0%',
                'genres'=>collect($game['genres'])->pluck('name')->implode(', '),
                'platforms'=>isset($game['platforms'])?collect($game['platforms'])->pluck('abbreviation')->implode(', '):null,
                'first_release_date'=>Carbon::parse ($game['first_release_date'])->format('M d,Y'),
                'trailer' => 'https://youtube.com/embed/'.$game['videos'][0]['video_id'],
                'social'=>[
                    'website'=>collect($game['websites'])->first(),
                    'facebook'=>collect($game['websites'])->filter(function ($website){
                        return str::contains($website['url'],'facebook');
                    })->first(),
                    'twitter'=>collect($game['websites'])->filter(function ($website){
                        return str::contains($website['url'],'twitter');
                    })->first(),
                    'instagram'=>collect($game['websites'])->filter(function ($website){
                        return str::contains($website['url'],'instagram');
                    })->first(),
                ]
    
                ]);
        })->toArray();
        
        //dd($temp);
        
    }
}
