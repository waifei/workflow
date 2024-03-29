<?php

/*
 * Copyright (C) 2015 ralph
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace OmniFlow\BPMN;

use OmniFlow;
use OmniFlow\WFCase;

/**
 * Description of Task
 *
 * @author ralph
 */
class Task extends Node
{
	public function isTask()
	{
		return true;
	}
	public function Init()
	{
		parent::Init();
                if (($this->type=='sendTask')||($this->type=='receiveTask'))
                {
                    $this->subType='message';
                    $this->hasMessage=true;
                }
		if (count($this->outflows)==1)
		{
			$out=$this->outflows[0];
			$next=$out->toNode;
		}
		return;
	}
	public function describe()
	{
	
		return parent::describe();
	}
	function NeedToWait(WFCase\WFCaseItem $caseItem,$input,$from)
        {
 		OmniFlow\Context::Log(LOG,"Checking wait for Node: class:Task type: $this->type - $this->label - $this->id $this->actionScript");
            
		if ($this->type=="receiveTask")
		{
                    if ($from===null)
                            return false;
                    
                    if ($from->type=='messageFlow')
                            return false;
                    else
                        return true;
		}
		if (($this->type=="userTask")||($this->type=="task"))
		{
                        return true; // Don't continue
		}
                return false;
        }
        public function requiresAccessRules()
        {
            if (($this->type=="userTask")||($this->type=="task"))
            {
                if ($this->isExecutable())
                    return true;
            }
            return false;
        }
	function Run(WFCase\WFCaseItem $caseItem,$input,$from)
	{
 		OmniFlow\Context::Log(LOG,"Run Node: class:Task type: $this->type - $this->label - $this->id $this->actionScript");
                

    		if ($this->requiresAccessRules())
		{
                    $this->checkAccessRules($caseItem);
                    $caseItem->UserTake();
    
                 	$actionView=$caseItem->getActionView($postForm);
                
                        \OmniFlow\Context::Log(INFO,"actionView:$actionView");

                        if ($actionView===true)
                                return true;	
                            \OmniFlow\ActionManager::defaultForm($caseItem);
                        return true;
		}
		
		if ($this->actionScript!="")
                {
			$ret=ActionManager::ExecuteAction($this->actionScript,$caseItem);
                }
		
		if ($this->customFunction!="")
		{
//			echo 'calling '.$this->customFunction;
			$ret=call_user_func($this->customFunction,$this,$caseItem,$input,$from);
//			echo '<br /> result='.$this->result;
		}
			
		return true;
	}
}
