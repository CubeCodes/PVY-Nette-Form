<?php

namespace App\Presenters;

use Nette\Database\Context;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Nette\Utils\DateTime;

class NabidkaPresenter extends BasePresenter {
    private $database;
    
    function __construct(Context $database) {
        $this->database = $database;
    }
    
    public function renderDefault($order = 'id') {
        /* Do proměnné zajezdy se z tabulky nabidka
           vypíší všechny záznamy */
        $this->template->zajezdy = $this->database
                                        ->table('nabidka')
                                        ->order($order)    
                                        ->fetchAll();
    }

    public function renderZaznam($id) {
        /* Do proměnné zajezdy se z tabulky nabidka
           vypíší všechny záznamy */
        $this->template->zajezd = $this->database
                                       ->table('nabidka')
                                       ->get($id);                               
    }
    
    public function renderInsert() {        
    }
    
    public function renderUpdate($id) {
        $data = $this->database->table('nabidka')
                       ->get($id);
               Debugger::barDump($data);
        $data = $data->toArray();
        $data['datum'] = $data['datum']->format('Y-m-d');
        $this['nabidkaForm']->setDefaults($data);
    }
    
    public function actionDelete($id) { 
        $row = $this->database->table('nabidka')
                       ->get($id);
        if($row->delete()){ $this->flashMessage('Záznam byl smazán'); }
        else{ $this->flashMessage('Záznam nemohl byt smazán'); }
        
        $this->redirect("default");
    }
    
    protected function createComponentNabidkaForm()
    {
        $form = new Form;
        $form->addText('destinace', 'Místo pobytu:')
             ->addRule(Form::MIN_LENGTH,'Musí být zadáno aspoň 5 znaků!',5)   
             ->setRequired('Destinace je povinný údaj');
        $form->addTextArea('popis', 'Podrobnější popis:')
             ->setRequired(false);
        $form->addInteger('cena', 'Cena:')
             ->setDefaultValue(10000)   
             ->addRule(Form::RANGE,'Musí být v rozsahu %d až %d',[1000,999999]);
        $form->addText('datum', 'Datum:')
             ->setAttribute('type', 'date');   
        $form->addInteger('delka', 'Počet nocí:')
             ->setDefaultValue(7)   
             ->addRule(Form::RANGE,'Musí být v rozsahu %d až %d',[1,99]);
        $doprava = [
            'auto' => 'Auto',
            'autobus' => 'Autobus',
            'letadlo' => 'Letadlo',
            'kombinovaná' => 'Kombinová'
        ];
        $form->addSelect('doprava', 'Doprava:', $doprava)
             ->setDefaultValue('autobus');
        $form->addSubmit('submit', 'Potvrdit');
        $form->onSuccess[] = [$this, 'nabidkaFormSucceeded'];
        return $form;
    }

    // volá se po úspěšném odeslání formuláře
    public function nabidkaFormSucceeded(Form $form, \stdClass $values)
    {
        Debugger::barDump($values);
        if($this->database->table('nabidka')->insert($values)){
            $this->flashMessage('Byl ulozen novy zaznam');
        }
        else {
            $this->flashMessage('Zaznam nebyl ulozen');
        }
        
        $this->redirect('Nabidka:default');
    }    
    
}
