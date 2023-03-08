<?php

namespace App\Http\Livewire;

use App\Models\user_preference;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Illuminate\Support\Str;


class UserGames2 extends Component
{   public $game1;
    public $rating1=0;

    public $popularGames=[]; 
    public $tempPopularGames=[]; 

    public $currentGame=0;

    public function goToNextPage(){ 
         /* Log::info(sizeof($this->popularGames)); */
         $temp=user_preference::where('user_id', Auth::user()->id)
             ->where('game_id', '$this->game1')->get();
             if($temp!=null){
                Log::info('already exist'.$this->currentGame); 
                $temp->fresh();
                $this->increment();
             }
             else{
         if($this->rating1!=0){

                /* user_preference::create([
                    'user_id'=>Auth::user()->id,
                    'game_id'=>$this->game1,
                    'rating'=>$this->rating1,
                ]);
            $this->increment(); */
                Log::info('$temp->game_id'); 

            
        }
        else{
            $this->increment();
        }
    }
    }

     public function increment(){ 
        Log::info('in skip'.$this->currentGame); 
        $this->currentGame++;
        $this->tempPopularGames=$this->popularGames[$this->currentGame];
        $this->game1=$this->tempPopularGames['id'];
        return view('livewire.user-games2');
    }

    
    public function loadPopularGames()
    {
        $after=Carbon::now()->addMonth(2)->timestamp;

        $popularGamesCleaned  = 
             Http::withHeaders(config('services.igdb'))
            ->send('POST', 'https://api.igdb.com/v4/games?', 
            [
                'body' => 'fields name , cover.url , first_release_date , platforms.abbreviation , rating , aggregated_rating,slug;
                where category = (0,9) & platforms = (48)  & rating>85 
                &  first_release_date < '.$after.';
                sort rating desc;
                limit 50;'
            ]
            )->json();
        
         
        //dump($this->cleanView($popularGamesCleaned));
        $this->popularGames =$this->cleanView($popularGamesCleaned);
        $this->tempPopularGames=$this->popularGames[0];
        $this->game1=$this->tempPopularGames['id'];
        }

    public function cleanView($games){
        return collect($games)->map(function($game){
            return collect($game)->merge([
                'coverImageUrl'=>Str::replaceFirst('thumb','cover_big', $game['cover']['url']),
                'rating'=>isset($game['rating'])?round($game['rating']).'%':null,
                'platforms'=>collect($game['platforms'])->pluck('abbreviation')->implode(', ')
            ]);
        })->toArray();
    }
    public function render()
    {
        return view('livewire.user-games2');
    }

    public function mount2()
    {
        $this->game1=$this->tempPopularGames['id'];
    }
}
