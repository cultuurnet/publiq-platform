<?php

namespace Publiq\InsightlyLink;

enum InsightlyType: string
{
    case Contact = 'contact';
    case Opportunity = 'opportunity';
    case Project = 'project';
    case Organization = 'organization';
}
