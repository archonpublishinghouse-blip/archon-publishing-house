(()=>{
    'use strict';

    const storageKey='archon.book.visitor.v1';
    const clean=value=>String(value||'').trim().replace(/\s+/g,' ');
    const readName=()=>{try{return clean(localStorage.getItem(storageKey));}catch{return '';}};
    const writeName=value=>{try{localStorage.setItem(storageKey,value);}catch{}};
    const removeName=()=>{try{localStorage.removeItem(storageKey);}catch{}};

    document.addEventListener('DOMContentLoaded',()=>{
        const book=document.querySelector('[data-preview]');
        if(!book)return;

        const stage=book.querySelector('[data-stage]');
        const sheets=[...book.querySelectorAll('[data-sheet]')];
        const sheetCount=sheets.length;
        const templates=[...book.querySelectorAll('template[data-book-page]')];
        const previous=book.querySelector('[data-prev]');
        const next=book.querySelector('[data-next]');
        const reset=book.querySelector('[data-reset]');
        const progress=book.querySelector('[data-progress]');
        const dialog=document.querySelector('[data-book-welcome]');
        const form=document.querySelector('[data-book-welcome-form]');
        const input=document.querySelector('[data-book-name-input]');
        const error=document.querySelector('[data-book-name-error]');
        let currentState=0;
        let targetState=0;
        let isAnimating=false;
        let activeSheet=null;
        let direction='none';
        let transitionToken=0;
        let timer=0;
        let visitorName=readName();
        let ready=Boolean(visitorName);

        const animationStyle=document.createElement('style');
        animationStyle.textContent=`
            .preview-turn-layer{position:absolute;inset:0;z-index:200;overflow:hidden;pointer-events:none;perspective:2500px}
            .preview-turn-overlay,.preview-turn-underlay{position:absolute;top:0;width:50%;height:100%;overflow:hidden;padding:clamp(1.7rem,3.1vw,4rem);background:#f2ebd0;background-image:radial-gradient(rgba(91,69,28,.13) .5px,transparent .7px);background-size:7px 7px;color:#0a2223}
            .preview-turn-overlay{z-index:2;backface-visibility:hidden;-webkit-backface-visibility:hidden;transform-style:preserve-3d}
            .preview-turn-underlay{z-index:1}
            .preview-turn-overlay.is-left,.preview-turn-underlay.is-left{left:0;transform-origin:right center}
            .preview-turn-overlay.is-right,.preview-turn-underlay.is-right{right:0;transform-origin:left center}
            .preview-turn-overlay.next-out{animation:previewNextOut .32s cubic-bezier(.42,0,1,1) both}
            .preview-turn-overlay.next-in{animation:previewNextIn .32s cubic-bezier(0,0,.2,1) both}
            .preview-turn-overlay.previous-out{animation:previewPreviousOut .32s cubic-bezier(.42,0,1,1) both}
            .preview-turn-overlay.previous-in{animation:previewPreviousIn .32s cubic-bezier(0,0,.2,1) both}
            .preview-settled-layer{position:absolute;inset:0;z-index:10;display:grid;grid-template-columns:1fr 1fr;pointer-events:none}
            .preview-settled-page{position:relative;overflow:hidden;padding:clamp(1.7rem,3.1vw,4rem);background:#f2ebd0;background-image:radial-gradient(rgba(91,69,28,.13) .5px,transparent .7px);background-size:7px 7px;color:#0a2223}
            .preview-settled-page:empty{background:transparent}
            @keyframes previewNextOut{from{transform:rotateY(0deg)}to{transform:rotateY(-89.9deg)}}
            @keyframes previewNextIn{from{transform:rotateY(89.9deg)}to{transform:rotateY(0deg)}}
            @keyframes previewPreviousOut{from{transform:rotateY(0deg)}to{transform:rotateY(89.9deg)}}
            @keyframes previewPreviousIn{from{transform:rotateY(-89.9deg)}to{transform:rotateY(0deg)}}
            @media(max-width:800px){.preview-turn-overlay,.preview-turn-underlay{left:0!important;right:auto!important;width:100%}.preview-settled-layer{display:block}.preview-settled-page.is-left{display:none}.preview-settled-page.is-right{width:100%;height:100%}}
            @media(prefers-reduced-motion:reduce){.preview-turn-overlay{animation-duration:.01ms!important}}
        `;
        document.head.append(animationStyle);

        const pageIds=templates.map(template=>template.dataset.bookPage);
        sheets.slice(1,9).forEach(sheet=>sheet.style.display='none');
        const settledLayer=document.createElement('div');
        const settledLeft=document.createElement('article');
        const settledRight=document.createElement('article');
        settledLayer.className='preview-settled-layer';
        settledLeft.className='preview-settled-page is-left';
        settledRight.className='preview-settled-page is-right';
        settledLayer.append(settledLeft,settledRight);
        stage.prepend(settledLayer);

        const cover=book.querySelector('[data-face="cover"]');
        const insideFront=book.querySelector('[data-face="inside-front"]');
        const insideBack=book.querySelector('[data-face="inside-back"]');
        const backCover=book.querySelector('[data-face="back-cover"]');
        const colophon=book.querySelector('[data-face="page-16"]');
        const coverTitle=document.createElement('h1');
        const coverByline=document.createElement('p');
        const coverImprint=document.createElement('p');
        cover.replaceChildren();
        coverTitle.textContent='YOUR EBOOK JOURNEY';
        coverImprint.textContent='Brought to Life by Archon Publishing House';
        cover.append(coverTitle,coverByline,coverImprint);
        insideFront.textContent='Inside Front Cover';
        insideBack.textContent='Inside Back Cover';
        backCover.textContent='ARCHON PUBLISHING HOUSE';
        colophon.textContent='Colophon';

        const appendPage=(target,pageNumber)=>{
            target.replaceChildren();
            if(pageNumber===16){
                const heading=document.createElement('h2');
                const copy=document.createElement('p');
                heading.textContent='Colophon';
                copy.textContent='A quiet endpaper for your eBook journey.';
                target.append(heading,copy);
                return;
            }
            if(pageNumber<1||pageNumber>15)return;
            const template=templates.find(item=>item.dataset.bookPage===pageIds[pageNumber-1]);
            if(template)target.append(template.content.cloneNode(true));
        };
        const pagesForState=state=>{
            if(state===1)return {left:0,right:1};
            if(state>=2&&state<=8)return {left:state*2-2,right:state*2-1};
            if(state===9)return {left:16,right:0};
            return {left:0,right:0};
        };
        const renderSettledSpread=state=>{
            const pages=pagesForState(state);
            appendPage(settledLeft,pages.left);
            appendPage(settledRight,pages.right);
            personalize();
        };
        const updateCoverVisibility=state=>{
            sheets[0].style.visibility=state<=1?'visible':'hidden';
            sheets[9].style.visibility=state>=9?'visible':'hidden';
        };

        const personalizeWithin=root=>{
            root.querySelectorAll('[data-book-display-name]').forEach(node=>{
                node.textContent=visitorName||'Future Author';
            });
        };
        const personalize=()=>{
            coverByline.textContent=visitorName?`Written by ${visitorName}`:'Created for a Future Author';
            personalizeWithin(book);
        };
        const setTransforms=state=>{
            sheets.forEach((sheet,index)=>sheet.classList.toggle('is-flipped',index<state));
        };
        const applyRestingStack=state=>{
            sheets.forEach((sheet,index)=>{
                sheet.style.zIndex=String(index<state?index+1:sheetCount-index);
            });
            if(state<=1)sheets[0].style.zIndex='30';
            if(state>=9)sheets[9].style.zIndex='30';
        };
        const setTransformsImmediately=state=>{
            sheets.forEach(sheet=>sheet.style.transition='none');
            setTransforms(state);
            void stage.offsetWidth;
            sheets.forEach(sheet=>sheet.style.transition='');
        };
        const label=state=>{
            if(state===0)return 'Cover';
            if(state===10)return 'Back cover';
            if(state===9)return 'End';
            if(state===1)return 'Page 1 of 15';
            const first=state*2-2;
            return `Pages ${first}–${first+1} of 15`;
        };
        const draw=()=>{
            book.dataset.state=String(currentState);
            previous.disabled=!ready||isAnimating||currentState===0;
            next.disabled=!ready||isAnimating||currentState===10;
            progress.textContent=label(currentState);
            book.inert=!ready;
        };
        const showDialog=changing=>{
            input.value=changing?visitorName:'';
            error.textContent='';
            book.inert=true;
            if(dialog.showModal&&!dialog.open)dialog.showModal();else dialog.setAttribute('open','');
            setTimeout(()=>input.focus(),0);
        };
        const hideDialog=()=>{
            if(dialog.open)dialog.close();
            book.inert=false;
        };
        const clearOverlay=()=>stage.querySelector('.preview-turn-layer')?.remove();
        const finish=token=>{
            if(token!==transitionToken||!isAnimating)return;
            clearTimeout(timer);
            clearOverlay();
            currentState=targetState;
            activeSheet=null;
            direction='none';
            isAnimating=false;
            draw();
        };
        const copyReadablePage=(pageNumber,side,className='preview-turn-overlay')=>{
            const page=document.createElement('article');
            page.className=`${className} is-${side}`;
            appendPage(page,pageNumber);
            personalizeWithin(page);
            return page;
        };
        const animateOverlay=(overlay,className,token,onComplete)=>{
            let completed=false;
            const complete=source=>{
                if(completed||token!==transitionToken)return;
                completed=true;
                clearTimeout(timer);
                overlay.removeEventListener('animationend',ended);
                overlay.removeEventListener('animationcancel',cancelled);
                onComplete(source);
            };
            const ended=event=>{if(event.target===overlay)complete('animationend');};
            const cancelled=event=>{if(event.target===overlay)complete('animationcancel');};
            overlay.addEventListener('animationend',ended);
            overlay.addEventListener('animationcancel',cancelled);
            timer=setTimeout(()=>complete('fail-safe'),500);
            requestAnimationFrame(()=>overlay.classList.add(className));
        };
        const switchSettledSpread=state=>{
            setTransformsImmediately(state);
            applyRestingStack(state);
            renderSettledSpread(state);
            updateCoverVisibility(state);
            book.dataset.state=String(state);
            progress.textContent=label(state);
        };
        const turnInterior=(delta,target,token)=>{
            const currentPages=pagesForState(currentState);
            const targetPages=pagesForState(target);
            const outgoingSide=delta>0?'right':'left';
            const incomingSide=delta>0?'left':'right';
            const outgoingPage=delta>0?currentPages.right:currentPages.left;
            const incomingPage=delta>0?targetPages.left:targetPages.right;
            const revealedPage=delta>0?targetPages.right:targetPages.left;
            const retainedPage=delta>0?currentPages.left:currentPages.right;
            const layer=document.createElement('div');
            layer.className='preview-turn-layer';
            layer.setAttribute('aria-hidden','true');
            layer.inert=true;
            let underlay=copyReadablePage(revealedPage,outgoingSide,'preview-turn-underlay');
            const outgoing=copyReadablePage(outgoingPage,outgoingSide);
            layer.append(underlay,outgoing);
            stage.append(layer);
            animateOverlay(outgoing,delta>0?'next-out':'previous-out',token,()=>{
                outgoing.remove();
                switchSettledSpread(target);
                underlay.remove();
                underlay=copyReadablePage(retainedPage,incomingSide,'preview-turn-underlay');
                const incoming=copyReadablePage(incomingPage,incomingSide);
                layer.append(underlay,incoming);
                animateOverlay(incoming,delta>0?'next-in':'previous-in',token,()=>finish(token));
            });
        };
        const turnPhysicalCover=(delta,target,token)=>{
            const sheetIndex=delta>0?currentState:currentState-1;
            const sheet=sheets[sheetIndex];
            activeSheet=sheetIndex;
            sheet.style.zIndex=String(sheetCount+100);
            const finalizeCover=()=>{
                sheet.removeEventListener('transitionend',ended);
                clearTimeout(timer);
                currentState=target;
                sheet.style.zIndex='';
                applyRestingStack(currentState);
                renderSettledSpread(currentState);
                updateCoverVisibility(currentState);
                finish(token);
            };
            const ended=event=>{if(event.target===sheet&&event.propertyName==='transform')finalizeCover();};
            sheet.addEventListener('transitionend',ended);
            timer=setTimeout(finalizeCover,850);
            if(target===1||target===9){renderSettledSpread(target);updateCoverVisibility(target);}
            requestAnimationFrame(()=>{
                book.dataset.state=String(target);
                setTransforms(target);
            });
        };
        const move=delta=>{
            if(!ready||isAnimating)return;
            const target=currentState+delta;
            if(target<0||target>10)return;
            targetState=target;
            direction=delta>0?'forward':'backward';
            isAnimating=true;
            const token=++transitionToken;
            draw();
            if(matchMedia('(prefers-reduced-motion: reduce)').matches){
                switchSettledSpread(target);
                finish(token);
                return;
            }
            const isInterior=currentState>=1&&currentState<=9&&target>=1&&target<=9;
            if(isInterior)turnInterior(delta,target,token);else turnPhysicalCover(delta,target,token);
        };

        form.addEventListener('submit',event=>{
            event.preventDefault();
            if(event.submitter.matches('[data-book-guest]')){
                visitorName='';
                ready=true;
                personalize();
                hideDialog();
                draw();
                return;
            }
            const value=clean(input.value);
            if(value.length<2||value.length>60){
                error.textContent='Please enter a name between 2 and 60 characters.';
                return;
            }
            visitorName=value;
            writeName(value);
            ready=true;
            personalize();
            hideDialog();
            draw();
        });
        previous.addEventListener('click',()=>move(-1));
        next.addEventListener('click',()=>move(1));
        reset.addEventListener('click',()=>{
            if(isAnimating)return;
            currentState=targetState=0;
            setTransformsImmediately(0);
            applyRestingStack(0);
            renderSettledSpread(0);
            updateCoverVisibility(0);
            draw();
        });
        book.querySelector('[data-change]').addEventListener('click',()=>showDialog(true));
        book.querySelector('[data-remove]').addEventListener('click',()=>{
            visitorName='';
            removeName();
            ready=false;
            currentState=targetState=0;
            setTransformsImmediately(0);
            applyRestingStack(0);
            renderSettledSpread(0);
            updateCoverVisibility(0);
            personalize();
            draw();
            showDialog(false);
        });
        document.addEventListener('keydown',event=>{
            if(event.key==='ArrowRight')move(1);
            if(event.key==='ArrowLeft')move(-1);
        });

        personalize();
        setTransforms(0);
        applyRestingStack(0);
        renderSettledSpread(0);
        updateCoverVisibility(0);
        draw();
        if(!ready)showDialog(false);
    });
})();
